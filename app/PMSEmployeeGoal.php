<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoal extends Model
{
    //
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $table = "pms_employeegoal";
    protected $fillable = [
        "Id","SysPmsNumberId","EmployeeId","DepartmentId","Status","CreatedBy","created_at","EditedBy","updated_at"
    ];
}
