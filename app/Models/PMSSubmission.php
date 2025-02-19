<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PMSSubmission extends Model
{
    protected $table = 'pms_submission';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","EmployeeId","DepartmentId","DesignationId","SavedPMSOutcomeId","PMSOutcomeId","OutcomeDateTime","PayScale","BasicPay","GradeStepId","PaySlab","NewPayScale","NewBasicPay","NewPositionId","NewGradeStepId","NewDesignationId","NewLocation","FinalRemarks","PositionId","SectionId","WeightageForLevel1","WeightageForLevel2","Level2CriteriaType","SubmissionTime","FilePath","File2Path","CreatedBy","EditedBy","updated_at"];
}
