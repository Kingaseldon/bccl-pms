<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'mas_position';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","GradeId","SupervisorId","Name","DisplayOrder","CreatedBy","EditedBy","updated_at"];
}