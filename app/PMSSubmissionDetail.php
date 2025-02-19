<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class PMSSubmissionDetail extends Model
{
    protected $table = 'pms_submissiondetail';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","SubmissionId","AssessmentArea","Weightage","ApplicableToLevel2","DisplayOrder","SelfRating","Level1Rating","Level2Rating","CreatedBy","EditedBy","updated_at"];
}