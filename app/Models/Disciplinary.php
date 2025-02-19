<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disciplinary extends Model
{
    protected $table = 'rec_disciplinary';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","EmployeeId","ActionTakenBy","RecordDate","DepartmentId","PositionId","GradeStepId","DesignationLocation","Record","RecordDescription","CreatedBy","EditedBy","created_at","updated_at"];

}
