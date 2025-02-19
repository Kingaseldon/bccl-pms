<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasSupervisor extends Model
{
    protected $table = "mas_supervisor";
    protected $primaryKey = "Id";
    public $timestamps = false;
    protected $fillable = ["Id","Name","CreatedBy","EditedBy","updated_at"];
}

