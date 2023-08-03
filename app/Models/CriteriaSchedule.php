<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriteriaSchedule extends Model
{
    protected $fillable = [
        "schedule_id",
        "criteria_id",
        "start_date",
        "completion_date",
    ];
}
