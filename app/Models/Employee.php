<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-04
 * Time: 11:56 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = "mas_employee";
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ["Id","EmpId","Email","Extension","MobileNo","Name","Gender","CIDNo","ProfilePicPath","DesignationId","JobLocation","DateOfBirth","DateOfAppointment","SectionId","DepartmentId","GradeId","SupervisorId","PositionId","BasicPay","PayScale","GradeStepId","Qualification1","Qualification2","password","RoleId","HasChangedPassword","Status","remember_token","updated_at","CreatedBy","EditedBy","created_at"];
}
