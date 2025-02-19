<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'mas_department';
    protected $primaryKey = "Id";
    public $timestamps = false;
    protected $fillable = ["Id","ShortName","Name","Status","CreatedBy","EditedBy","updated_at"];
}