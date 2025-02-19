<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoalHistory extends Model
{
    //
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $table = "pms_employeegoalhistory";
    protected $fillable = [
        "Id","EmployeeGoalId","PMSStatusId","Remarks","StatusUpdateTime","StatusByEmployeeId"
    ];
}
