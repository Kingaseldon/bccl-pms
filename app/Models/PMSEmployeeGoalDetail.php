<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoalDetail extends Model
{
    //
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $table = "pms_employeegoaldetail";
    protected $fillable = [
        "Id",
        "EmployeeGoalId",
        "Type",
        "DisplayOrder",
        "Description",
        "Weightage",
        "Target",
        "Achievement",
        "SelfScore",
        "Level1_1Score",
        "Level1_2Score",
        "Level1_3Score",
        "Level1Score",
        "CreatedBy",
        "created_at",
        "EditedBy",
        "updated_at",
        "SelfRemarks",
        "Level1Remarks",
        "Level2Remarks"
    ];
    public function pmsEmployeeGoal()
    {
        return $this->belongsTo(PMSEmployeeGoal::class, "EmployeeGoalId", "Id");
    }
}
