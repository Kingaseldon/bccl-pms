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
use App\Models\Designation;


use App\Http\Controllers\Controller;

class DesignationController extends Controller
{
    public function getIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $perPage = 10;
        $name = $request->input('Name');

        $condition = "1=1";
        $parameters = [];

        if($name){
            $condition.=" and Name like ?";
            $parameters[] = "%$name%";
        }

        $designations = DB::table('mas_designation')
                            ->whereRaw("coalesce(Status,0)=1")
                            ->orderBy('Name')
                            ->whereRaw("$condition",$parameters)
                            ->paginate($perPage);
        return view('application.designationindex',['designations'=>$designations,'perPage'=>$perPage]);
    }
    public function getForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $designation = [new Designation()];
        $update = false;
        if($id){
            $update = true;
            $designation = Designation::find($id);
            if(!$designation){
                abort(404);
            }
        }

        return view('application.designationform')->with('designation',$designation)->with('update',$update);
    }
    public function postSave(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        //FORM VALIDATION
        $this->validate($request, [
            'Name' => 'required',
        ],
        [
            'Name.required'=>'Please type a Designation Name'
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
                $object = Designation::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)",['mas_designation',Auth::id(),0,$recordJson]);
                }
                $object->update();

            }else{
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::id();
                unset($inputs['Id']);
                $save  = true;
                $savedRecord = Designation::create($inputs);
                $id = $savedRecord->Id;
            }
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->with('errormessage',$e->getMessage());
        }
        if(isset($saveAudit) && $saveAudit) {
            $this->saveAuditTrail('mas_designation', $id);
        }
        DB::commit();
        return redirect('designationindex')->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
    }

    public function getDelete($id): \Illuminate\Http\RedirectResponse
    {
        try{
            $this->saveAuditTrail('mas_designation',$id,1);
            Designation::where('Id',$id)->delete();
        }catch(\Exception $e){
            $this->saveError($e,false);
            return back()->with('errormessage',"Designation could not be deleted because there are Employees or other records related to this department.");
        }
        return back()->with('successmessage','Record has been deleted');
    }
}
