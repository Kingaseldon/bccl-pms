<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PositionDepartmentRatingCriteria extends Model
{
    protected $table = 'mas_positiondepartmentratingcriteria';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","PositionDepartmentRatingId","Description","Weightage","ApplicableToLevel2","CreatedBy","EditedBy","updated_at"];
}
