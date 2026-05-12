<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

Route::get('/', [TaskController::class, 'publicCalendar'])->name('public.calendar');
Route::get('/tasks/events', [TaskController::class, 'events'])->name('tasks.events');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [TaskController::class, 'adminCalendar'])
    ->middleware('auth')
    ->name('admin.dashboard');

    Route::post('/admin/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/admin/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/admin/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/admin/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::delete('/admin/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

require __DIR__.'/auth.php';