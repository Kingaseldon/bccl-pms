<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PositionDepartment extends Model
{
    protected $table = 'mas_positiondepartment';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","DepartmentId","PositionId","CreatedBy"];
}
