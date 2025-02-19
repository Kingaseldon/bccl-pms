<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-04
 * Time: 11:58 AM
 */

namespace App\Http\Controllers\Application;


use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel; //FOR Excel:: usage
use Barryvdh\DomPDF\Facade as PDF; //FOR PDF:: usage

class EmployeeController extends Controller
{
    public function getIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $departmentId = $request->input('DepartmentId');
        $designationId = $request->input('DesignationId');
        $roleId = $request->input('RoleId');
        $name = $request->input('Name');
        $PABX = $request->input('PABX');
        $gradeStepId = $request->input('GradeStepId');
        $empId = $request->input('EmpId');

        $append = "1=1";
        $parameters = [];

        if($departmentId){
            $append.=" and T1.DepartmentId = ?";
            $parameters[] = $departmentId;
        }
        if($gradeStepId){
            $append.=" and T1.GradeStepId = ?";
            $parameters[] = $gradeStepId;
        }
        if($roleId){
            if($roleId == 'xx'){
                $append.=" and T1.PositionId not in (?,?,?)";
                $parameters[] = CONST_POSITION_MD;
                $parameters[] = CONST_POSITION_HOD;
                $parameters[] = CONST_POSITION_HOS;
            }else{
                $append.=" and T1.PositionId = ?";
                $parameters[] = $roleId;
            }

        }
        if($designationId){
            $append.=" and T1.DesignationId = ?";
            $parameters[] = $designationId;
        }
        if($name){
            $append.=" and T1.Name like ?";
            $parameters[] = "%$name%";
        }
        if($PABX){
            $append.=" and T1.PABX = ?";
            $parameters[] = $PABX;
        }
        if($empId){
            $append.=" and T1.EmpId = ?";
            $parameters[] = "$empId";
        }

        $perPage = 15;
        $employees = DB::table('mas_employee as T1')
                        ->join('mas_department as T2','T2.Id','=','T1.DepartmentId')
                        ->join('sys_roles as T3','T3.Id','=','T1.RoleId')
                        ->leftJoin('mas_position as T4','T4.Id','=','T1.PositionId')
                        ->leftJoin('mas_designation as T5','T5.Id','=','T1.DesignationId')
                        ->select('T1.Id','T1.CIDNo','T1.EmpId','T1.Name','T2.Name as Department','T4.Name as Position','T3.Name as Role','T5.Name as DesignationLocation','T1.Email','T1.Extension','T1.Status')
                        ->whereRaw("$append",$parameters)
                        ->whereRaw("coalesce(T1.Extension,0)<>1234")
                        ->orderBy('T1.Name')/*->orderBy('T1.DesignationLocation')*/
                        ->paginate($perPage);

        $departments = DB::table('mas_department')->orderBy('Name')->get(array('Id','Name'));
        $designationLocations = DB::table('mas_employee as T1')
                                    ->join('mas_department as T2','T2.Id','=','T1.DepartmentId')
                                    ->join('mas_designation as T3','T3.Id','=','T1.DesignationId')
                                    ->orderBy('T3.Name')
                                    ->whereNotNull('T1.DesignationId')
                                    ->distinct()
                                    ->selectRaw("T3.Id,T3.Name, GROUP_CONCAT(distinct concat('\"',T1.DepartmentId,'\"') SEPARATOR ',') as DepartmentIds")
                                    ->groupBy('T3.Id')->get();

        $gradeSteps = DB::select("select Id, Name from mas_gradestep order by Name");

        $roles = DB::select("select Id, Name from sys_roles order by Name");
        return view('application.employeeindex',['gradeSteps'=>$gradeSteps,'employees'=>$employees,'roles'=>$roles,'perPage'=>$perPage,'departments'=>$departments,'designationLocations'=>$designationLocations]);
    }
    public function getForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $update = false;
        $employee = [new Employee];
        if($id){
            $update = true;
            $employee = Employee::find($id);
            if(!$employee){
                abort(404);
            }
        }
        $departments = DB::table('mas_department')->orderBy('Name')->get(array('Id','Name'));
        $designationLocations = DB::table('mas_designation as T3')
            ->orderBy('T3.Name')
            ->distinct()
            ->selectRaw("T3.Id,T3.Name")->get();
        $roles = DB::select("select Id, Name from sys_roles order by Name");
        $positions = DB::select("select T1.Id, T2.Name as Grade, T3.Name as Supervisor, concat(coalesce(T2.Name,'All'),case when T3.Id is not null then concat(' - ',T3.Name) else '' end) as PositionName, T1.Name, T1.DisplayOrder, (select GROUP_CONCAT(concat(T3.Id,'_',T3.Name,'_',coalesce(T4.Id,0)) order by T3.Name SEPARATOR ', ') from mas_positiondepartment T2 join mas_department T3 on T3.Id = T2.DepartmentId left join mas_positiondepartmentrating T4 on T4.PositionDepartmentId = T2.Id where T2.PositionId = T1.Id) as Departments from mas_position T1 left join mas_gradestep T2 on T2.Id = T1.GradeStepId left join mas_supervisor T3 on T3.Id = T1.SupervisorId where T1.Id <> ? order by Departments, PositionName",[CONST_POSITION_MD]);
        $gradeSteps = DB::table('mas_gradestep as T1')
            ->whereRaw("coalesce(T1.Status,0)=1")
            ->select('T1.Id','T1.PayScale','T1.Name')
            ->orderBy('T1.Status','DESC')
            ->orderBy(DB::raw('SUBSTR(T1.Name,1,2)'))
            ->orderBy(DB::raw("CAST(TRIM(SUBSTR(T1.Name,LENGTH(T1.Name)-1,2)) AS INT)"))
            ->get();
        $grades = DB::select("select Id, Name from mas_grade where coalesce(IsManagerialRole,0) = 1");
        $supervisorLevels = DB::select("select Id, Name from mas_supervisor");
        $sections = DB::table('mas_section')->orderBy('DepartmentId')->orderBy('Name')->get(array('Id','DepartmentId','Name'));
        return view('application.employeeform',['supervisorLevels'=>$supervisorLevels,'employee'=>$employee,'gradeSteps'=>$gradeSteps,'sections'=>$sections,'positions'=>$positions,'update'=>$update,'roles'=>$roles,'departments'=>$departments,'designationLocations'=>$designationLocations,'grades'=>$grades]);
    }
    public function postSave(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        //FORM VALIDATION
        $this->validate($request,
            [
                'Name' => 'required',
                'DesignationId' => 'required',
                'EmpId' => 'required',
                'CIDNo' => 'required',
                'DepartmentId' => 'required',
//                'SectionId' => 'required',
                'JobLocation' => 'required',
                'DateOfBirth' => 'required',
                'DateOfAppointment' => 'required',
                'GradeStepId' => 'required',
                'BasicPay' => 'required',
                'RoleId' => 'required',
                'Email' => 'required',
                'MobileNo' => 'required',
            ],
            [
                'Name.required'=>'Please type a Employee Name',
                'DesignationId.required'=>'Please select a Designation',
                'EmpId.required'=>'Employee Id field is required',
                'CIDNo.required'=>'CID field is required',
                'DepartmentId.required'=>'Please select a Department',
//                'SectionId.required'=>'Please select a Section',
                'JobLocation.required'=>'Job Location field is required',
                'DateOfBirth.required'=>'Date of Birth field is required',
                'DateOfAppointment.required'=>'Date of Appointment field is required',
                'GradeStepId.required'=>'Grade/Step field is required',
                'BasicPay.required'=>'Basic Pay field is required',
                'RoleId.required'=>'Please select a Role',
                'Email.required'=>'Email field is required',
                'MobileNo.required'=>'Mobile No. field is required',
            ]
        );
        //END
        $inputs = $request->input();
        $gradeStepId = $inputs['GradeStepId'];
        $supervisorId = $inputs['SupervisorId'];


//        $positionIdQuery = DB::table('mas_position')->where('GradeStepId',$gradeStepId)->where('SupervisorId',$supervisorId)->pluck('Id');
//        $positionId = isset($positionIdQuery[0])?$positionIdQuery[0]:NULL;
//        $inputs['PositionId'] = $positionId;
        if(!$inputs['SectionId']) {
            $inputs['SectionId'] = NULL;
        }
        if(!$inputs['Extension']) {
            $inputs['Extension'] = NULL;
        }
        if(!$inputs['Qualification1']) {
            $inputs['Qualification1'] = NULL;
        }
        if(!$inputs['Qualification2']) {
            $inputs['Qualification2'] = NULL;
        }
        $save = false;
        DB::beginTransaction();
        try{
            if($inputs['Id']){
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = Employee::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)",['mas_employee',Auth::id(),0,$recordJson]);
                }
                $object->update();
            }else{
                $saveAudit = true;
                $inputs['password'] = Hash::make($inputs['Password']);
                $inputs['CreatedBy'] = Auth::id();
                unset($inputs['Id']);
                $save  = true;
                $savedRecord = Employee::create($inputs);
                $id = $savedRecord->Id;
            }
        }catch(\Exception $e){
            DB::rollBack();
            return back()->with('errormessage',$e->getMessage());
        }

        DB::commit();
        if(isset($saveAudit) && $saveAudit){
            $this->saveAuditTrail('mas_employee',$id);
        }
        if(isset($request->redirect)){
            $redirect = $request->redirect;
            return redirect($redirect)->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
        }
        return redirect('employeeindex')->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
    }

    public function getDelete($id): \Illuminate\Http\RedirectResponse
    {
        try{
            $this->saveAuditTrail('mas_employee',$id,1);
            Employee::where('Id',$id)->delete();
        }catch(\Exception $e){
            $this->saveError($e,false);
            return back()->with('errormessage',"Employee could not be deleted because there are files under this employee.");
        }
        return back()->with('successmessage','Record has been deleted');
    }
    public function postResetPassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $id = $request->input('Id');
        $password = urldecode($request->input('Password'));

        $hashedPassword = Hash::make($password);
        $name = DB::table('mas_employee')->where('Id',$id)->pluck('Name');
        $email = DB::table('mas_employee')->where('Id',$id)->pluck('Email');
        DB::table('mas_employee')->where('Id',$id)->update(['password'=>$hashedPassword]);

        $mailBody = "<p>Dear ".$name[0]."<br/><br/>Your password has been reset to <strong>$password</strong><br/>Please login to TICL PMS System using this password.</p>";
        $this->sendMail($email[0],$mailBody,'Online PMS Password has been reset successfully!');

        return back()->with('successmessage',"Password has been reset successfully for ".$name[0]);
    }
    public function exportEmployeesToExcel(){
        $employees = DB::select("select Name, Extension from mas_employee");
//        dd($employees);
//        Excel::create('New file', function($excel) use($employees){
//            $excel->sheet('New sheet', function($sheet) use($employees) {
//                $sheet->loadView('exports.employees',['employees'=>$employees]);
//            });
//        })->download('xlsx');

//        $pdf = PDF::loadView('exports.employees', ['employees'=>$employees]);
//        return $pdf->download('employees.pdf');
    }
}

