<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $table = "doc_category";
    protected $fillable = [
        'Id','DepartmentId','Name','Status','CreatedBy','EditedBy','created_at','updated_at'
    ];
}