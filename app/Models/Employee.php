<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['name'];

    public function leadingTasks()
    {
        return $this->hasMany(Task::class, 'team_lead_id');
    }

    public function memberTasks()
    {
        return $this->belongsToMany(Task::class, 'employee_task')
            ->withTimestamps();
    }
}