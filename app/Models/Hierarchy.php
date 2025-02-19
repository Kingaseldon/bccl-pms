<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hierarchy extends Model
{
    protected $table = 'mas_hierarchy';
    protected $primaryKey = "Id";
    public $timestamps = false;
    protected $fillable = ["Id","EmployeeId","ReportingLevel1EmployeeId","ReportingLevel2EmployeeId","DisplayOrder","CreatedBy","EditedBy","updated_at"];
}
