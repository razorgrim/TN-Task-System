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
        $tasks = Task::with(['teamLead', 'members'])->get();

        return $tasks->map(function ($task) {

            $start = $task->task_date;

            $end = null;

            if ($task->end_date) {
                $end = date('Y-m-d', strtotime($task->end_date . ' +1 day'));
            }

            $teamLead =
                $task->teamLead
                    ? $task->teamLead->name
                    : 'No Team Lead';

            $members =
                $task->members
                    ->pluck('name')
                    ->implode(', ');

            return [

                'id' => $task->id,

                'title' => $task->title,

                'start' => $start,

                'end' => $end,

                'url_update' => route('tasks.update', $task->id),

                'url_delete' => route('tasks.destroy', $task->id),

                'extendedProps' => [

                    'display_title' =>
                        $task->title .
                        ' | Lead: ' . $teamLead,

                    'description' => $task->description,

                    'team_lead_id' => $task->team_lead_id,

                    'team_lead_name' => $teamLead,

                    'member_ids' =>
                        $task->members->pluck('id'),

                    'member_names' => $members,

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
            'description' => 'nullable|string',
            'team_lead_id' => 'nullable|exists:employees,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:employees,id',
            'task_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:task_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $this->validateEmployeeAvailability(
            $request->team_lead_id,
            $request->member_ids ?? [],
            $request->task_date,
            $request->end_date
        );

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'team_lead_id' => $request->team_lead_id,
            'task_date' => $request->task_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'status' => $request->status,
        ]);

        $task->members()->sync($request->member_ids ?? []);

        return redirect()->route('admin.dashboard')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_lead_id' => 'nullable|exists:employees,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:employees,id',
            'task_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:task_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $this->validateEmployeeAvailability(
            $request->team_lead_id,
            $request->member_ids ?? [],
            $request->task_date,
            $request->end_date,
            $task->id
        );

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'team_lead_id' => $request->team_lead_id,
            'task_date' => $request->task_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'status' => $request->status,
        ]);

        $task->members()->sync($request->member_ids ?? []);

        return redirect()->route('admin.dashboard')->with('success', 'Task updated successfully.');
    }

    private function validateEmployeeAvailability($teamLeadId, array $memberIds, $taskDate, $endDate = null, $ignoreTaskId = null)
    {
        $employeeIds = collect($memberIds);

        if ($teamLeadId) {
            $employeeIds->push($teamLeadId);
        }

        $employeeIds = $employeeIds->filter()->unique()->values();

        if ($employeeIds->isEmpty()) {
            return;
        }

        $start = $taskDate;
        $end = $endDate ?: $taskDate;

        foreach ($employeeIds as $employeeId) {
            $conflict = Task::query()
                ->where(function ($query) use ($start, $end) {
                    $query->whereDate('task_date', '<=', $end)
                        ->whereDate(\DB::raw('COALESCE(end_date, task_date)'), '>=', $start);
                })
                ->when($ignoreTaskId, function ($query) use ($ignoreTaskId) {
                    $query->where('id', '!=', $ignoreTaskId);
                })
                ->where(function ($query) use ($employeeId) {
                    $query->where('team_lead_id', $employeeId)
                        ->orWhereHas('members', function ($memberQuery) use ($employeeId) {
                            $memberQuery->where('employees.id', $employeeId);
                        });
                })
                ->exists();

            if ($conflict) {
                $employee = Employee::find($employeeId);

                abort(422, ($employee?->name ?? 'Employee') . ' is already assigned on this date.');
            }
        }
    }
    public function updateDates(Request $request)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.task_date' => 'required|date',
            'tasks.*.end_date' => 'nullable|date',
        ]);

        foreach ($request->tasks as $taskData) {
            $task = Task::find($taskData['id']);

            $task->update([
                'task_date' => $taskData['task_date'],
                'end_date' => $taskData['end_date'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task dates updated successfully.',
        ]);
    }
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Task deleted successfully.');
    }
    public function employeeAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'ignore_task_id' => 'nullable|exists:tasks,id',
        ]);

        $date = $request->date;
        $endDate = $request->end_date ?: $date;

        $employees = Employee::orderBy('name')->get();

        $availability = $employees->map(function ($employee) use ($date, $endDate, $request) {
            $taskQuery = Task::query()
                ->whereDate('task_date', '<=', $endDate)
                ->whereDate(\DB::raw('COALESCE(end_date, task_date)'), '>=', $date)
                ->when($request->ignore_task_id, function ($query) use ($request) {
                    $query->where('id', '!=', $request->ignore_task_id);
                })
                ->where(function ($query) use ($employee) {
                    $query->where('team_lead_id', $employee->id)
                        ->orWhereHas('members', function ($memberQuery) use ($employee) {
                            $memberQuery->where('employees.id', $employee->id);
                        });
                });

            $assignedTask = $taskQuery->first();

            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'available' => !$assignedTask,
                'assigned_task' => $assignedTask ? $assignedTask->title : null,
            ];
        });

        return response()->json($availability);
    }
}