<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeReport extends Model
{
    protected $table = 'time_reports';
    protected $fillable = ['employee_id', 'hours', 'date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
