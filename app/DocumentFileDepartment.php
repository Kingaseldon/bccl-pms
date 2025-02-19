<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class DocumentFileDepartment extends Model
{
    protected $table = "doc_filedepartment";
    protected $fillable = [
        'Id','FileId','DepartmentId','CreatedBy','EditedBy','created_at','updated_at'
    ];
}