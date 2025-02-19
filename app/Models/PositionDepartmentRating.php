<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PositionDepartmentRating extends Model
{
    protected $table = 'mas_positiondepartmentrating';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","PositionDepartmentId","WeightageForLevel1","WeightageForLevel2","Level2CriteriaType","CreatedBy","EditedBy","updated_at"];
}
