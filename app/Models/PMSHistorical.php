<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PMSHistorical extends Model
{
    protected $table = 'pms_historical';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","CIDNo","EmpId","PMSNumberId","PMSSubmissionId","PMSScore","Level2PMSScore","PMSResult","PMSRemarks"];
}
