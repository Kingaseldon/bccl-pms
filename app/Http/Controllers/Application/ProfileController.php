<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Intervention\Image\Facades\Image as Image;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function getIndex($employeeId = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $hasParam = true;
        if(!$employeeId){
            $employeeId = Auth::id();
            $hasParam = false;
        }
        $details = DB::select("select T1.EmpId,T1.Qualification1,T1.Qualification2,T1.Status,T4.GradeId,T1.CIDNo,T1.DateOfAppointment as DateOfRegularization, (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel1EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level1Name,
 (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel2EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level2Name, T4.Name as GradeStep, T4.PayScale,
 T1.Extension, T1.MobileNo, T1.DateOfBirth, T1.DateOfAppointment,T1.ProfilePicPath,T1.Extension,T1.Name,B.Name as DesignationLocation,
 T2.Name as Department,A.Name as Section, concat(Z1.Name,case when Z2.Id is null then '' else concat(' - Reporting to ',Z2.Name) end) as
 Position from mas_employee T1 left join mas_designation B on B.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left
 join mas_section A on A.Id = T1.SectionId left join (mas_position T3 join mas_gradestep Z1 on Z1.Id = T3.GradeStepId left join mas_supervisor Z2
 on Z2.Id = T3.SupervisorId) on T3.Id = T1.PositionId left join mas_gradestep T4 on T4.Id = T1.GradeStepId /*left join (mas_hierarchy W1
 join mas_employee W2 on W2.Id = W1.ReportingLevel1EmployeeId left join mas_designation V1 on V1.Id = W2.DesignationId left join
 mas_employee W3 left join mas_designation V2 on V2.Id = W3.DesignationId on W3.Id = W1.ReportingLevel2EmployeeId) on W1.EmployeeId = T1.Id
  */where T1.Id = ?",[$employeeId]);
        return view('application.profile')->with('details',$details)->with('hasParam',$hasParam);
    }
    public function saveProfilePic(Request $request): \Illuminate\Http\RedirectResponse
    {
        $file = $request->file('Image');
        $extension = $file->getClientOriginalExtension();
        if(!in_array($extension,['png','jpg','jpeg','gif','PNG','JPG','JPEG','GIF'])){
            return back()->with('errormessage','Wrong file format! Please upload an image.');
        }
        $image = Image::make($request->file('Image'));
        $width = $image->width();
        if($width > 500){
            $image->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $path = 'profilepics/'.Auth::id().'_'.date('YmdHis').'.png';
        $image->save($path);
        $object = Employee::find($request->Id);
        $object->ProfilePicPath = $path;
        $object->update();

        return back()->with('successmessage','Profile Pic has been uploaded!');
    }
}
