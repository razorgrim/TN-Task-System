<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'task_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'status',
    ];
}