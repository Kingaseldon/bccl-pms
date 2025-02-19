<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PMSSubmissionHistory extends Model
{
    protected $table = 'pms_submissionhistory';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","SubmissionId","PMSStatusId","StatusUpdateTime","StatusByEmployeeId","CreatedBy","EditedBy","updated_at"];
}
