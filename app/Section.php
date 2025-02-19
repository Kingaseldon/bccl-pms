<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'mas_section';
    protected $primaryKey = "Id";
    public $timestamps = false;
    protected $fillable = ["Id","Name","DepartmentId","Status","CreatedBy","EditedBy","updated_at"];
}