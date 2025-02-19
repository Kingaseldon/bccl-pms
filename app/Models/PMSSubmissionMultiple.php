<?php
/**
 * Created by PhpStorm.
 * User: SWM
 * Date: 6/21/2019
 * Time: 4:09 PM
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class PMSSubmissionMultiple extends Model
{
    protected $table = 'pms_submissionmultiple';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","SubmissionId","AppraisedByEmployeeId","Remarks","Status","ForLevel","FilePath","CreatedBy","EditedBy","updated_at"];
}
