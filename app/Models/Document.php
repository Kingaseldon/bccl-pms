<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = "doc_file";
    protected $fillable = [
        'Id','CategoryId','DepartmentId','VisibilityLevel','FilePath','Name','Status','CreatedBy','EditedBy','created_at','updated_at'
    ];
}
