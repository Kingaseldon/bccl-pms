<?php


namespace App\Http\Controllers\Files;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentFileDepartment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class FileController extends Controller
{
    public function getDisplay(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $userDepartmentId = Auth::user()->DepartmentId;

        $departmentId = $request->DepartmentId;
        $categoryId = $request->CategoryId;
        $fileName = $request->FileName;

        $condition = "1=1";
        $params = [];
        if($departmentId){
            $condition.=" and T2.DepartmentId = ?";
            $params[] = $departmentId;
        }else{
            $departmentId = "zz";
        }
        if($categoryId){
            $condition.=" and T1.CategoryId = ?";
            $params[] = $categoryId;
        }
        if($fileName){
            $condition.=" and T1.Name like ?";
            $params[] = "%$fileName%";
        }
        $data['perPage'] = 200;
        $data['files'] = DB::table('doc_file as T1')
            ->join('doc_category as T2','T2.Id','=','T1.CategoryId')
            ->join('mas_department as T3','T3.Id','=','T2.DepartmentId')
            ->select(['T1.Name','T1.Id','T1.FilePath','T3.Name as Department','T2.Name as Category',DB::raw("(select GROUP_CONCAT(B.DepartmentId SEPARATOR ',') from doc_filedepartment B where B.FileId = T1.Id) as Depts")])
            ->orderBy('T3.Name')
            ->orderBy('T2.Name')
            ->orderBy('T1.Name')
            ->whereRaw("$condition",$params)
            ->paginate($data['perPage']);

        $data['categories'] = DB::select("select T1.Id, T1.Name, T1.DepartmentId from doc_category T1 join doc_file T2 on T2.CategoryId = T1.Id where T1.Status = 1 and T1.DepartmentId = ? group by T1.Id",[$departmentId]);
        $data['departments'] = DB::select("select T1.Id, T1.Name,T1.Name from mas_department T1 join doc_category T2 on T2.DepartmentId = T1.Id join doc_file T3 on T3.CategoryId = T2.Id where T1.Status = 1 group by T1.Id");

        return view('files.filedisplay',$data);
    }
    public function getCategoryIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $departmentId = $request->DepartmentId;
        $name = $request->Name;
        $parameters = [];
        $condition = "1=1";
        if($departmentId){
            $condition .= " and T1.DepartmentId = ?";
            $parameters[] = $departmentId;
        }
        if($name){
            $condition .= " and T1.Name like ?";
            $parameters[] = "%$name%";
        }

        $data['perPage'] = 15;
        $data['departments'] = DB::select("select Id, Name from mas_department order by Name");
        $data['categories'] = DB::table("doc_category as T1")->join('mas_department as T2','T1.DepartmentId','=','T2.Id')->orderBy('T2.Name')->orderBy('T1.Name')->whereRaw("$condition",$parameters)->select(['T1.Id','T2.Name as Department','T1.Name',DB::raw("case when T1.Status = 1 then 'Active' else 'In-active' end as Status")])->paginate($data['perPage']);
        return view('files.categoryindex',$data);
    }
    public function getCategoryForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $data['update'] = false;
        if($id){
            $data['update'] = true;
            $data['category'] = DB::select("select Id, DepartmentId,Status, Name from doc_category where Id = ?",[$id]);
            if(empty($data['category'])){
                abort(404);
            }
        } else {
            $data['category'] = [new DocumentCategory];
        }
        $data['departments'] = DB::select("select Id, Name from mas_department order by ShortName");
        return view('files.categoryform',$data);
    }
    public function saveCategory(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $inputs = $request->input();
        $rules = [
            'DepartmentId' => "required",
            "Name" => "required"
        ];
        $messages = [
            'DepartmentId.required' => "The Department field is required",
            'Name.required' => "The Name field is required"
        ];
        $validation = $this->validate($request,$rules,$messages);
        $action = 'saved';
        DB::beginTransaction();
        try{
            if($request->Id){
                $action = "updated";
                $updateObject = DocumentCategory::find($request->Id);
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $updateObject->fill($inputs);
                $changes = $updateObject->getDirty();
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $inputs['Id'];
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)",['doc_category',Auth::id(),0,$recordJson]);
                }
                $updateObject->update();
            } else {
                $saveAudit = true;
                $inputs['Id'] = UUID();
                $inputs['CreatedBy'] = Auth::id();
                DocumentCategory::create($inputs);
            }
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->withInput()->with('errormessage',"Something went wrong!");
        }
        DB::commit();
        if(isset($saveAudit) && $saveAudit){
            $this->saveAuditTrail('doc_category',$inputs['Id']);
        }
        return redirect('filecategoryindex')->with('successmessage',"Record has been $action");
    }
    public function getDeleteCategory($id): \Illuminate\Http\RedirectResponse
    {
        try{
            $this->saveAuditTrail('doc_category',$id,1);
            DocumentCategory::where('Id',$id)->delete();
        }catch(\Exception $e){
            $this->saveError($e,false);
            return back()->with('errormessage',"Document Category could not be deleted because there are Documents or other records related to this Document Category.");
        }
        return back()->with('successmessage','Record has been deleted');
    }
    public function getFileIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $departmentId = $request->DepartmentId;
        $categoryId = $request->CategoryId;
        $fileName = $request->FileName;

        $condition = "1=1";
        $params = [];
        if($departmentId){
            $condition.=" and T2.DepartmentId = ?";
            $params[] = $departmentId;
        }
        if($categoryId){
            $condition.=" and T1.CategoryId = ?";
            $params[] = $categoryId;
        }
        if($fileName){
            $condition.=" and T1.Name like ?";
            $params[] = "%$fileName%";
        }
        $data['perPage'] = 15;
        $data['files'] = DB::table('doc_file as T1')
                    ->join('doc_category as T2','T2.Id','=','T1.CategoryId')
                    ->join('mas_department as T3','T3.Id','=','T2.DepartmentId')
                    ->select(['T1.Name','T1.Id','T1.FilePath','T3.Name as Department',DB::raw("case when (select count(Id) from mas_department where Id not in (select B.DepartmentId from doc_filedepartment B where B.FileId = T1.Id and B.DepartmentId <> 99)) = 0 then '1' else '0' end as OrgWide"),DB::raw("case when (select B.DepartmentId from doc_filedepartment B where B.FileId = T1.Id and B.DepartmentId = 99) = 99 then '1' else '0' end as VisibleToManagement"),DB::raw("(select GROUP_CONCAT(coalesce(A.Name,case when B.DepartmentId = 99 then 'Management Team' else '' end) SEPARATOR ', ') from doc_filedepartment B left join mas_department A on A.Id = B.DepartmentId where B.FileId = T1.Id) as Visibility"),'T2.Name as Category'])
                    ->orderBy('T3.Name')
                    ->orderBy('T2.Name')
                    ->orderBy('T1.Name')
                    ->whereRaw("$condition",$params)
                    ->paginate($data['perPage']);
        $data['categories'] = DB::select("select Id, Name, DepartmentId from doc_category where Status = 1");
        $data['departments'] = DB::select("select Id, Name,ShortName from mas_department where Status = 1");

        return view('files.fileindex',$data);
    }
    public function getFileForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $data['update'] = false;
        if($id){
            $data['update'] = true;
            $data['file'] = DB::select("select T1.Id, T1.CategoryId, T2.DepartmentId, T1.Name, T1.FilePath, T1.VisibilityLevel, T1.Status from doc_file T1 join doc_category T2 on T2.id = T1.CategoryId where T1.Id = ?",[$id]);
            $data['filedepartments'] = DB::table('doc_filedepartment as T1')->where('T1.FileId',$id)->pluck("DepartmentId");
            if(empty($data['file'])){
                abort(404);
            }
        }else{
            $data['file'] = [new Document];
            $data['filedepartments'] = [];
        }

        $data['categories'] = DB::select("select Id, Name, DepartmentId from doc_category where Status = 1");
        $data['departments'] = DB::select("select Id, Name,ShortName from mas_department where Status = 1");
        return view('files.fileform',$data);
    }
    public function saveFile(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $action = 'updated';
        $inputs = $request->except("VisibilityLevel");
        if($inputs['Id']){
            $rules = [
                'CategoryId' => "required",
                "Name" => "required"
            ];
            $messages = [
                'CategoryId.required' => "The Category field is required",
                'Name.required' => "The Name field is required"
            ];
        }else{
            $rules = [
                'CategoryId' => "required",
                'File' => "required|file|mimes:pdf",
                "Name" => "required"
            ];
            $messages = [
                'CategoryId.required' => "The Category field is required",
                'File.required' => "The File field is required",
                'File.file' => "The File must be a valid file", //LARAVEL FILE VALIDATION
                'File.mimes' => "Wrong file format. Please upload pdf document only", //LARAVEL FILE TYPE VALIDATION
                'Name.required' => "The Name field is required"
            ];
        }

        $validation = $this->validate($request,$rules,$messages);
        unset($inputs['DepartmentId']);
        if($request->hasFile('File')){
            if($inputs['Id']){
                $oldFile = DB::table('doc_file')->where('Id',$inputs['Id'])->value('FilePath');
                File::delete($oldFile);
            }

            $directory = 'documents/'.date('Y').'/'.date('m');
            $file = $request->file('File');
            $extension = $file->getClientOriginalExtension();
            if(!$this->in_arrayi($extension,['pdf'])){
                return back()->with('errormessage','Wrong file format. Please upload pdf document only');
            }

            $fileName = $inputs['Name'].'_'.$request->DepartmentId.'_'.randomString().randomString().'.'.$file->getClientOriginalExtension();
            $file->move($directory,$fileName);
            $inputs['FilePath'] = $directory.'/'.$fileName;
        }
        DB::beginTransaction();
        try{
            if($inputs['Id']){
                $updateObject = Document::find($inputs['Id']);
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $updateObject->fill($inputs);
                $changes = $updateObject->getDirty();
                if(!(count($changes) == 2 && array_key_exists('updated_at',$changes) && array_key_exists('EditedBy',$changes)) && !(count($changes) == 1 && array_key_exists('updated_at',$changes))){
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $inputs['Id'];
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)",['doc_file',Auth::id(),0,$recordJson]);
                }
                DocumentFileDepartment::where('FileId',$inputs['Id'])->delete();

                $updateObject->update();
                if($request->has("VisibilityLevel")){
                    foreach($request->input('VisibilityLevel') as $departmentId):
                        if($departmentId != 100){
                            $mapInputs['Id'] = UUID();
                            $mapInputs['DepartmentId'] = $departmentId;
                            $mapInputs['FileId'] = $inputs['Id'];
                            $mapInputs['CreatedBy'] = Auth::id();
                            DocumentFileDepartment::create($mapInputs);
                        }
                    endforeach;
                } else {
                    DB::insert("INSERT INTO doc_filedepartment (Id,FileId,DepartmentId,CreatedBy) SELECT UUID(),?,Id,? from mas_department where coalesce(Status,0)=1",[$inputs['Id'],Auth::id()]);
                }

            }else{
                $action = 'saved';
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::id();
                $inputs['Id'] = UUID();

                Document::create($inputs);
                if($request->has("VisibilityLevel")){
                    foreach($request->input('VisibilityLevel') as $departmentId):
                        if($departmentId != 100){
                            $mapInputs['Id'] = UUID();
                            $mapInputs['DepartmentId'] = $departmentId;
                            $mapInputs['FileId'] = $inputs['Id'];
                            $mapInputs['CreatedBy'] = Auth::id();
                            DocumentFileDepartment::create($mapInputs);
                        }
                    endforeach;
                } else {
                    DB::insert("INSERT INTO doc_filedepartment (Id,FileId,DepartmentId,CreatedBy) SELECT UUID(),?,Id,? from mas_department where coalesce(Status,0)=1",[$inputs['Id'],Auth::id()]);
                }

            }
        }catch(\Exception $e){
            DB::rollBack();
            $this->saveError($e,false);
            return back()->withInput()->with('errormessage',"Something went wrong!");
        }
        DB::commit();
        if(isset($saveAudit) && $saveAudit){
            $this->saveAuditTrail('doc_file',$inputs['Id']);
        }
        $redirectPage = $request->has('RedirectPage')?$request->get('RedirectPage'):1;
        return redirect('fileindex?page='.$redirectPage)->with('successmessage',"Record has been $action");
    }
    public function getDeleteFile($id): \Illuminate\Http\RedirectResponse
    {
        try{
            $this->saveAuditTrail('doc_file',$id,1);
            $filePath = DB::table('doc_file')->where('Id',$id)->value('FilePath');
            Document::where('Id',$id)->delete();
        }catch(\Exception $e){
            $this->saveError($e,false);
            return back()->with('errormessage',"Document could not be deleted.");
        }
        File::delete($filePath);
        return back()->with('successmessage','Record has been deleted');
    }
    public function downloadFile(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $file = $request->input('file');
        return response()->download($file);
    }
    public function getRender(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $data['file'] = Crypt::decrypt($request->z);
        $data['name'] = Crypt::decrypt($request->w);
        //if(Auth::user()->EmpId == "714"): dd($data); endif;
        return view('files.filerender',$data);
    }
    public function fetchCategoriesOnDept(Request $request): \Illuminate\Http\JsonResponse
    {
        $deptId = $request->deptId;
        $categories = DB::select("select distinct T1.Id, T1.Name from doc_category T1 join doc_file T2 on T2.CategoryId = T1.Id join doc_filedepartment T3 on T3.FileId = T2.Id where T1.DepartmentId = ? and T3.DepartmentId = ?",[$deptId,Auth::user()->DepartmentId]);
        return response()->json($categories);
    }
}
