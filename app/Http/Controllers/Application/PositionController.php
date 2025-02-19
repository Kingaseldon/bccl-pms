<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;
use App\Models\PositionDepartment;
use App\Models\PositionDepartmentRating;
use App\Models\PositionDepartmentRatingCriteria;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;
use App\Models\Position;

use App\Http\Controllers\Controller;

class PositionController extends Controller
{
    public function getIndex(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $positions = DB::select("select T1.Id, coalesce(T2.Name,'All') as Grade, T3.Name as Supervisor, concat(coalesce(T2.Name,'All'),case when T3.Id is not null then concat(' - ',T3.Name) else '' end) as PositionName, T1.Name, T1.DisplayOrder, (select GROUP_CONCAT(concat(coalesce(T3.Id,0),'_',T3.Name,'_',coalesce(T4.Id,0)) order by T3.Name SEPARATOR '| ') from mas_positiondepartment T2 join mas_department T3 on T3.Id = T2.DepartmentId left join mas_positiondepartmentrating T4 on T4.PositionDepartmentId = T2.Id where T2.PositionId = T1.Id) as Departments from mas_position T1 left join mas_gradestep T2 on T2.Id = T1.GradeStepId left join mas_supervisor T3 on T3.Id = T1.SupervisorId where T1.Id <> ? order by T2.StartingSalary DESC",[CONST_POSITION_MD]);
        return view('application.positionindex',['positions'=>$positions]);
    }
    public function getForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $departments = $this->fetchActiveDepartments();
        $positionDepartmentMaps = [];
        $position = new Position();
        $update = false;
        if($id){
            $update = true;
            $position = Position::find($id);
            if(!$position){
                abort(404);
            }
            $positionDepartmentMaps = DB::table('mas_positiondepartment')->where('PositionId',$id)->pluck('DepartmentId')->toArray();
        }
        $grades = DB::select("select Id, Name from mas_gradestep order by StartingSalary DESC");
        $supervisorLevels = DB::select("select Id, Name from mas_supervisor");
        return view('application.positionform')->with('grades',$grades)->with('supervisorLevels',$supervisorLevels)->with('positionDepartmentMaps',$positionDepartmentMaps)->with('position',$position)->with('update',$update)->with('departments',$departments);
    }
    public function postSave(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $departmentIds = $request->input('DepartmentId');
        if(empty($departmentIds)){
            return back()->withInput();
        }
        $idArray = [];
        $inputs = $request->input();
        $save = false;
        DB::beginTransaction();
        try{
            if(!$inputs['GradeStepId']){
                $inputs['GradeStepId'] = NULL;
            }
            if($inputs['Id']){
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = Position::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)",['mas_position',Auth::id(),0,$recordJson]);
                }
                $object->update();
            }else{
                $saveAudit = true;
                $inputs['Id'] = UUID();
                $id = $inputs['Id'];
                $inputs['CreatedBy'] = Auth::id();
                $save  = true;
                Position::create($inputs);
            }
            $mappedDepartmentIds = PositionDepartment::where('PositionId',$inputs['Id'])->pluck('DepartmentId')->toArray();

            $mappedDepartmentIds = json_decode(json_encode($mappedDepartmentIds));
            foreach($mappedDepartmentIds as $mappedDepartmentId):
                if(!in_array($mappedDepartmentId,$departmentIds)):
                    PositionDepartment::where('PositionId',$inputs['Id'])->where('DepartmentId',$mappedDepartmentId)->delete();
                endif;
            endforeach;
            $departmentIds = array_diff($departmentIds,$mappedDepartmentIds);

            foreach($departmentIds as $departmentId):
                $mapArray['Id'] = UUID();
                $mapArray['DepartmentId'] = $departmentId;
                $mapArray['CreatedBy'] = Auth::id();
                $mapArray['PositionId'] = $inputs['Id'];
                $idArray[] = $mapArray['Id'];
                PositionDepartment::create($mapArray);
            endforeach;
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->with('errormessage',$e->getMessage());
        }

        DB::commit();
        if(isset($saveAudit) && $saveAudit == true){
            $this->saveAuditTrail('mas_position',$id);
        }
        if(!empty($idArray)){
            foreach($idArray as $id):
                $this->saveAuditTrail('mas_positiondepartment',$id);
            endforeach;
        }
        return redirect('positionindex')->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
    }

    public function getDelete($id): \Illuminate\Http\RedirectResponse
    {
        DB::beginTransaction();
        try{
            if(!in_array($id,[CONST_POSITION_MD,CONST_POSITION_HOD,CONST_POSITION_HOS])){
                $this->saveAuditTrail('mas_position',$id,1);
                Position::where('Id',$id)->delete();
            }else{
                return back()->with('errormessage',"Position could not be deleted because it is needed for functioning of system.");
            }
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->with('errormessage',"Position could not be deleted because there are Employees or other records related to this position.");
        }
        DB::commit();
        return back()->with('successmessage','Record has been deleted');
    }
    public function fetchForm($deptId,$positionId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $update = true;
        $departmentName = DB::table('mas_department')->where('Id',$deptId)->pluck('Name');
        $positionName = DB::table('mas_position as T1')->leftJoin('mas_gradestep as T2','T2.Id','=','T1.GradeStepId')->leftJoin('mas_supervisor as T3','T3.Id','=','T1.SupervisorId')->where('T1.Id',$positionId)->selectRaw("concat(T2.Name,' - ',T3.Name) as Name")->pluck('Name');

        $positionDepartmentId = DB::table('mas_positiondepartment')->where('DepartmentId',$deptId)->where('PositionId',$positionId)->pluck('Id');

        if(!$departmentName || !$positionName || !$positionDepartmentId){
            abort(404);
        }
        $departmentName = $departmentName[0];
        $positionName = $positionName[0];

        $positionDepartmentId = $positionDepartmentId[0];
        $rating = DB::table('mas_positiondepartmentrating')->where('PositionDepartmentId',$positionDepartmentId)->get(array('Id','WeightageForLevel1','Level2CriteriaType','WeightageForLevel2'));
        if(empty($rating)){
            $update = false;
        }
        $ratingCriteria = DB::table('mas_positiondepartmentratingcriteria as T1')
                            ->join('mas_positiondepartmentrating as T2','T2.Id','=','T1.PositionDepartmentRatingId')
                            ->where('T2.PositionDepartmentId',$positionDepartmentId)
                            ->get(array('Description','Weightage','ApplicableToLevel2'));
        if(!count($ratingCriteria)){
            $ratingCriteria = [new PositionDepartmentRatingCriteria()];
        }
        return view('application.criteriaform')
                    ->with('update',$update)
                    ->with('rating',$rating)
                    ->with('departmentName',$departmentName)
                    ->with('positionName',$positionName)
                    ->with('positionDepartmentId',$positionDepartmentId)
                    ->with('ratingCriteria',$ratingCriteria);
    }
    public function saveCriteria(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $inputs = $request->except('criteria');
        $criteriaInputs = $request->input('criteria');
        $criteriaId = [];
        $save = true;
        $hasCriteria = false;
        DB::beginTransaction();
        try{
            if($inputs['Id']){
                $save = false;
                $ratingTotal = doubleval($inputs['WeightageForLevel1']) + doubleval($inputs['WeightageForLevel2']);
                if($ratingTotal != 100){
                    DB::rollBack();
                    return back()->withInput()->with('errormessage','The total for Rating Weightage should be 100.');
                }
                if(!$inputs['WeightageForLevel2']){
                    $inputs['WeightageForLevel2'] = NULL;
                }
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $updateObject = PositionDepartmentRating::find($inputs['Id']);
                $updateObject->fill($inputs);
                $changes = $updateObject->getDirty();
                $id = $inputs['Id'];
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    $saveAudit = true;
                }
                $updateObject->update();
            }else{
                $saveAudit = true;
                $inputs['Id'] = UUID();
                $id = $inputs['Id'];
                $inputs['CreatedBy'] = Auth::id();
                PositionDepartmentRating::create($inputs);
            }
            if(!$save){
                PositionDepartmentRatingCriteria::where('PositionDepartmentRatingId',$inputs['Id'])->delete();
            }
            $assessmentTotal = 0;
            foreach($criteriaInputs as $key=>$value):
                if($value['Description']){
                    $hasCriteria = true;
                }
                $value['Id'] = UUID();
                $criteriaId[] = $value['Id'];
                $value['PositionDepartmentRatingId'] = $inputs['Id'];
                $value['CreatedBy'] = Auth::id();
                $assessmentTotal += doubleval($value['Weightage']);
                PositionDepartmentRatingCriteria::create($value);

            endforeach;
            if($assessmentTotal != 100){
                DB::rollBack();
                return back()->withInput()->with('errormessage','The total for Assessment Area Weightage should be 100.');
            }
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->with('errormessage','Something went wrong!');
        }
        if(!$hasCriteria){
            DB::rollBack();
            return back()->withInput()->with('errormessage','Please set criteria in order to proceed!');
        }
        DB::commit();
        if(isset($saveAudit) && $saveAudit == true){
            $this->saveAuditTrail('mas_positiondepartmentrating',$id);
        }
        if(!empty($criteriaId)){
            foreach($criteriaId as $id):
                $this->saveAuditTrail('mas_positiondepartmentratingcriteria',$id);
            endforeach;
        }
        return redirect('positionindex')->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
    }
}

