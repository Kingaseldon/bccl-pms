<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;
use App\Models\Disciplinary;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;

class DisciplinaryController extends Controller
{
    public function getIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $notAdmin = false;
        $perPage = 10;
        $departments = $this->fetchActiveDepartments();
        $departmentId = $request->input('DepartmentId');
        $name = $request->input('Name');
        $recordDate = $request->input('RecordDate');

        $condition = '1=1';
        $parameters = [];
        if($departmentId){
            $condition.=" and T1.DepartmentId = ?";
            $parameters[] = $departmentId;
        }
        if($name){
            $condition.=" and T2.Name like ?";
            $parameters[] = "%$name%";
        }
        if($recordDate){
            $condition.=" and T1.RecordDate = ?";
            $parameters[] = $recordDate;
        }
        if(Auth::user()->RoleId == 2){
            $notAdmin = true;
            $condition.=" and T1.EmployeeId = ?";
            $parameters[] = Auth::id();
        }


        if($request->has('export') && $request->input('export') == 'excel'){
            $disciplinaryRecords = DB::table('rec_disciplinary as T1')
                ->join('mas_employee as T2','T2.Id','=','T1.EmployeeId')
                ->join('mas_designation as O','O.Id','=','T2.DesignationId')
                ->join('mas_department as T3','T3.Id','=','T1.DepartmentId')
                ->leftJoin('mas_position as T4','T4.Id','=','T1.PositionId')
                ->leftJoin('mas_gradestep as T5','T5.Id','=','T4.GradeStepId')
                ->leftJoin('mas_supervisor as T6','T6.Id','=','T4.SupervisorId')
                ->leftJoin('mas_gradestep as T7','T7.Id','=','T2.GradeStepId')
                ->whereRaw("$condition",$parameters)
                ->orderBy('T1.RecordDate','DESC')
                ->select('T1.Id','T1.Record','T1.ActionTakenBy','T1.DesignationLocation as SavedDesignation','T1.EmployeeId','T1.RecordDescription','O.Name as Designation','T2.CIDNo','T2.EmpId','T2.Name as Employee',DB::raw("T7.Name as Position"),'T3.Name as Department','T1.RecordDate')
                ->get();
            Excel::create("Disciplinary Records_".date('Y_m_d_H_i_s'),function($excel) use ($disciplinaryRecords) {
                $excel->sheet("Sheet",function($sheet) use ($disciplinaryRecords){
                    $sheet->loadView('exports.disciplinaryrecord',['disciplinaryRecords'=>$disciplinaryRecords]);
                });
            })->download('xlsx');
        }
        $disciplinaryRecords = DB::table('rec_disciplinary as T1')
                                ->join('mas_employee as T2','T2.Id','=','T1.EmployeeId')
                                ->join('mas_designation as O','O.Id','=','T2.DesignationId')
                                ->join('mas_department as T3','T3.Id','=','T1.DepartmentId')
                                ->leftJoin('mas_position as T4','T4.Id','=','T1.PositionId')
                                ->leftJoin('mas_gradestep as T5','T5.Id','=','T4.GradeStepId')
                                ->leftJoin('mas_supervisor as T6','T6.Id','=','T4.SupervisorId')
                                ->leftJoin('mas_gradestep as T7','T7.Id','=','T2.GradeStepId')
                                ->whereRaw("$condition",$parameters)
                                ->orderBy('T1.RecordDate','DESC')
                                ->select('T1.Id','T1.Record','T1.ActionTakenBy','T1.DesignationLocation as SavedDesignation','T1.EmployeeId','T1.RecordDescription','O.Name as Designation','T2.CIDNo','T2.EmpId','T2.Name as Employee',DB::raw("T7.Name as Position"),'T3.Name as Department','T1.RecordDate')
                                ->paginate($perPage);
        return view('application.disciplinaryindex',['departments'=>$departments,'notAdmin'=>$notAdmin,'perPage'=>$perPage,'disciplinaryRecords'=>$disciplinaryRecords]);
    }
    public function getForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $departments = $this->fetchActiveDepartments();
        $disciplinaryRecord = new Disciplinary();
        $employees = [];
        $update = false;
        if($id){
            $update = true;
            $disciplinaryRecord = Disciplinary::find($id);

            if(!$disciplinaryRecord){
                abort(404);
            }
            $employees = $this->getDepartmentEmployees($disciplinaryRecord['DepartmentId']);
        }
        $allEmployees = $this->getAllEmployees();
        return view('application.disciplinaryform')->with('allEmployees',$allEmployees)->with('employees',$employees)->with('disciplinaryRecord',$disciplinaryRecord)->with('update',$update)->with('departments',$departments);
    }
    public function saveDisciplinary(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        //FORM VALIDATION
        $this->validate($request, [
            'RecordDate' => 'required',
            'EmployeeId' => 'required',
            'Record' => 'required',
            'RecordDescription' => 'required',
        ],
        [
            'RecordDate.required'=>'Please enter a Record Date',
            'EmployeeId.required'=>'Please select an Employee',
            'Record.required'=>'Please enter a Record Nature',
            'RecordDescription.required'=>'Please enter a Record Description',
        ]);
        //END
        $inputs = $request->input();
        $save = false;
        DB::beginTransaction();
        try{
            $employeeId = $inputs['EmployeeId'];
            $employeeDetails = DB::table('mas_employee as T1')->join('mas_designation as T2','T2.Id','=','T1.DesignationId')->where('T1.Id',$employeeId)->get(array('T1.PositionId','T2.Name as DesignationLocation'));
            $inputs['PositionId'] = $employeeDetails[0]->PositionId;
            $inputs['DesignationLocation'] = $employeeDetails[0]->DesignationLocation;
            $inputs['ActionTakenBy'] = ($inputs['ActionTakenBy'])?:NULL;
            if($inputs['Id']){
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = Disciplinary::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)",['rec_disciplinary',Auth::id(),0,$recordJson]);
                }
                $object->update();
            }else{
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::id();
                $inputs['Id'] = UUID();
                $save  = true;
                $id = $inputs['Id'];
                Disciplinary::create($inputs);
            }
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->with('errormessage',$e->getMessage());
        }

        DB::commit();
        if(isset($saveAudit) && $saveAudit){
            $this->saveAuditTrail('rec_disciplinary',$id);
        }
        return redirect('disciplinaryindex')->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
    }

    public function getDelete($id): \Illuminate\Http\RedirectResponse
    {
        $this->saveAuditTrail('rec_disciplinary',$id,1);
        Disciplinary::where('Id',$id)->delete();
        return back()->with('successmessage','Record has been deleted');
    }
}

