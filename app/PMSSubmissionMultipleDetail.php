<?php
/**
 * Created by PhpStorm.
 * User: SWM
 * Date: 6/21/2019
 * Time: 4:09 PM
 */

namespace App;
use Illuminate\Database\Eloquent\Model;


class PMSSubmissionMultipleDetail extends Model
{
    protected $table = 'pms_submissionmultipledetail';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","SubmissionMultipleId","SubmissionDetailId","Score","CreatedBy","EditedBy","updated_at"];
}