<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'team_lead_id',
        'task_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'status',
    ];

    public function teamLead()
    {
        return $this->belongsTo(Employee::class, 'team_lead_id');
    }

    public function members()
    {
        return $this->belongsToMany(Employee::class, 'employee_task')
            ->withTimestamps();
    }
}