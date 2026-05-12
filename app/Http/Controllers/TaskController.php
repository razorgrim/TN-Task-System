<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Employee;

class TaskController extends Controller
{
    public function publicCalendar()
    {
        return view('calendar.public');
    }

    public function adminCalendar()
    {
        $employees = Employee::orderBy('name')->get();

        return view('calendar.admin', compact('employees'));
    }

    public function events()
    {
        $tasks = Task::all();

        return $tasks->map(function ($task) {
            $start = $task->task_date;
            $endDate = $task->end_date ?: $task->task_date;

            if ($task->start_time) {
                $start .= 'T' . $task->start_time;
            }

            $end = $endDate;

            if ($task->end_time) {
                $end .= 'T' . $task->end_time;
            } else {
                $end = date('Y-m-d', strtotime($endDate . ' +1 day'));
            }

            return [
                'id' => $task->id,
                'title' => $task->title . ' - ' . ($task->assigned_to ?: 'Unassigned'),
                'start' => $start,
                'end' => $end,
                'url_update' => route('tasks.update', $task->id),
                'url_delete' => route('tasks.destroy', $task->id),
                'extendedProps' => [
                    'description' => $task->description,
                    'assigned_to' => $task->assigned_to,
                    'location' => $task->location,
                    'status' => $task->status,
                    'task_date' => $task->task_date,
                    'end_date' => $task->end_date,
                    'start_time' => $task->start_time,
                    'end_time' => $task->end_time,
                ],
            ];
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'task_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:task_date',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        Task::create($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'task_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:task_date',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Task deleted successfully.');
    }
}