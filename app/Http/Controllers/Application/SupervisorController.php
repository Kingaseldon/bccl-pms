<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MasSupervisor;

class SupervisorController extends Controller
{
    //
    public function getIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $data['supervisors'] = DB::table("mas_supervisor")->orderBy("Name")->get(["Id","Name"]);
        return view('application.supervisorindex',$data);
    }
    public function getForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $supervisor = [new MasSupervisor()];
        $update = false;
        if($id){
            $update = true;
            $supervisor = MasSupervisor::find($id);
            if(!$supervisor){
                abort(404);
            }
        }
        return view('application.supervisorform')->with('supervisor',$supervisor)->with('update',$update);
    }
    public function postSave(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        //FORM VALIDATION
        $this->validate($request, [
            'Name' => 'required',
        ],
            [
                'Name.required'=>'Please type a Department Name'
            ]);
        //END
        $inputs = $request->input();
        $save = false;
        DB::beginTransaction();
        try{
            if($inputs['Id']){
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = MasSupervisor::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)",['mas_supervisor',Auth::id(),0,$recordJson]);
                }
                $object->update();
            }else{
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::id();
                unset($inputs['Id']);
                $save  = true;
                $savedRecord = MasSupervisor::create($inputs);
                $id = $savedRecord->Id;
            }
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->with('errormessage',$e->getMessage());
        }
        DB::commit();
        if(isset($saveAudit) && $saveAudit){
            $this->saveAuditTrail('mas_supervisor',$id);
        }
        return redirect('supervisorindex')->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
    }
    public function getDelete($id): \Illuminate\Http\RedirectResponse
    {
        try{
            $this->saveAuditTrail('mas_supervisor',$id,1);
            MasSupervisor::where('Id',$id)->delete();
        }catch(\Exception $e){
            $this->saveError($e,false);
            return back()->with('errormessage',"Evaluation Group could not be deleted because there are Employees or other records related to this department.");
        }
        return back()->with('successmessage','Record has been deleted successfully');
    }

}

