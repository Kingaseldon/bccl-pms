<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $table = 'mas_designation';
    protected $primaryKey = "Id";
    public $timestamps = false;
    protected $fillable = ["Id","Name","Status","CreatedBy","EditedBy","updated_at"];
}
