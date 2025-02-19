<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\Controller;

class EmpDetailsController extends Controller
{
    public function getIndex($employeeId = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $isAppraiser = 0;
        $userPositionId = Auth::user()->PositionId;
        if(!in_array($userPositionId,[CONST_POSITION_HOD,CONST_POSITION_HOS,CONST_POSITION_MD])){
            if(!in_array($userPositionId,[CONST_POSITION_HOD,CONST_POSITION_HOS,CONST_POSITION_MD])){
                $isAppraiser = DB::table('mas_hierarchy as T1')->join('mas_employee as T2','T2.Id','=','T1.EmployeeId')->whereRaw("(T1.ReportingLevel1EmployeeId = ? or T1.ReportingLevel2EmployeeId = ?) and coalesce(T2.Status,0) = 1",[Auth::id(),Auth::id()])->count();
                if($isAppraiser > 0){
                    $userPositionId = CONST_POSITION_HOS;
                }
            }
        }

        $statusQuery = DB::table('sys_pmsnumber')->where('StartDate',"<=",date('Y-m-d'))->orderBy('StartDate','DESC')->take(1)->get(['Status','PMSNumber','StartDate']);
        $status = $statusQuery[0]->Status;
        $currentPMSNumber = $statusQuery[0]->PMSNumber;
        $currentPMSStartDate = $statusQuery[0]->StartDate;

        $units = [];
        $employees = [];

        if($userPositionId == CONST_POSITION_HOS){
            $type = 1;
            $sectionId = Auth::user()->SectionId;
            $employees = DB::select("select T1.Id, concat(T1.Name,', ',V.Name,' Department') as Name, T2.PMSOutcomeId, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId from (mas_employee T1 join mas_department V on V.Id = T1.DepartmentId join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTIme >= ? where coalesce(T1.Status,0) = 1 and (B.ReportingLevel1EmployeeId = ? or B.Reportinglevel2EmployeeId = ?) order by A.DisplayOrder,T1.Name",[$currentPMSStartDate,Auth::id(),Auth::id()]);
        }else if($userPositionId == CONST_POSITION_HOD){
            $type = 2;
            $units = DB::select("select distinct T1.Id, concat(T2.Name, ' | ', T1.Name) as Name from mas_section T1 join mas_department T2 on T2.Id = T1.DepartmentId join (mas_employee A join mas_hierarchy B on B.EmployeeId = A.Id) on A.SectionId = T1.Id where B.ReportingLevel1EmployeeId = ? or B.ReportingLevel2EmployeeId = ? and coalesce(T1.Status,0) = 1 and coalesce(A.Status,0) = 1 order by T1.Name",[Auth::id(),Auth::id()]);
            foreach($units as $section):
                $employees[$section->Id] = DB::select("select T1.Id,'' as Section, T1.Name, T2.PMSOutcomeId, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId from (mas_employee T1 join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTime >= ? where coalesce(T1.Status,0) = 1 and (B.ReportingLevel1EmployeeId = ? or B.ReportingLevel2EmployeeId = ?) and T1.SectionId = ? order by A.DisplayOrder,T1.Name",[$currentPMSStartDate,Auth::id(),Auth::id(),$section->Id]);
            endforeach;
        }else{
            $type = 3;
            $units = DB::select("select Id, Name from mas_department where coalesce(Status,0) = 1 order by Name");
            foreach($units as $department):
                if(Auth::user()->RoleId == 5){
                    $employees[$department->Id] = DB::select("select T1.Id, Z1.Name as Section, T2.PMSOutcomeId, T1.Name, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId, case when B.ReportingLevel2EmployeeId is null then 1 else 0 end as NoLevel2 from (mas_employee T1 left join mas_section Z1 on Z1.Id = T1.SectionId join mas_designation O on O.Id = T1.DesignationId join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTime >= ? where coalesce(T1.Status,0) = 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) group by T1.Id order by T1.Name",[$currentPMSStartDate,$department->Id,$department->Id]);
                }else{
                    $employees[$department->Id] = DB::select("select T1.Id, Z1.Name as Section, T2.PMSOutcomeId, T1.Name, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId, case when B.ReportingLevel2EmployeeId is null then 1 else 0 end as NoLevel2 from (mas_employee T1 left join mas_section Z1 on Z1.Id = T1.SectionId left join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTime >= ? where coalesce(T1.Status,0) = 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) group by T1.Id order by T1.Name",[$currentPMSStartDate,$department->Id,$department->Id]);
                }

            endforeach;
        }
        return view('application.employeehistorylist')->with('type',$type)->with('units',$units)->with('employees',$employees);
    }
    public function getDetails($employeeId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $details = DB::select("select T1.EmpId,T1.Status,T4.GradeId,T1.PositionId,T1.CIDNo,T1.DateOfAppointment as DateOfRegularization, (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel1EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level1Name,
 (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel2EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level2Name, T4.Name as GradeStep, T4.PayScale,
 T1.Extension, T1.MobileNo, T1.DateOfBirth, T1.DateOfAppointment,T1.ProfilePicPath,T1.Extension,T1.Name,B.Name as DesignationLocation,
 T2.Name as Department,A.Name as Section, Z2.Name as
 Position from mas_employee T1 left join mas_designation B on B.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left
 join mas_section A on A.Id = T1.SectionId left join mas_gradestep T4 on T4.Id = T1.GradeStepId left join (mas_position T3 left join mas_supervisor Z2
 on Z2.Id = T3.SupervisorId) on T3.Id = T1.PositionId /*left join (mas_hierarchy W1
 join mas_employee W2 on W2.Id = W1.ReportingLevel1EmployeeId left join mas_designation V1 on V1.Id = W2.DesignationId left join
 mas_employee W3 left join mas_designation V2 on V2.Id = W3.DesignationId on W3.Id = W1.ReportingLevel2EmployeeId) on W1.EmployeeId = T1.Id
  */where T1.Id = ?",[$employeeId]);
        if(count($details)==0){
            abort(404);
        }
        $cid = $details[0]->CIDNo;
        $history = DB::table('pms_historical as T1')->join('sys_pmsnumber as T2','T2.Id','=','T1.PMSNumberId')
            ->orderBy('T2.PMSNumber')
            ->where('T1.CIDNo',trim($cid))
            ->get(array('T2.PMSNumber','T2.StartDate','T1.PMSScore','T1.PMSResult','T1.PMSRemarks'));
        return view('application.employeehistory')->with('history',$history)->with('details',$details);
    }
}
