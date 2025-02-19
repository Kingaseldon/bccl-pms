<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-04
 * Time: 11:58 AM
 */

namespace App\Http\Controllers\SystemAdmin;


use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Input;
use Auth;
use App\Category;

class BugController extends Controller
{
    public function getIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $perPage = 10;
        $url = $request->input('URL');
        $fromDate = $request->input("FromDate");
        $toDate = $request->input("ToDate");
        $where = "1=1";
        $parameters = [];
        if($url){
            $where.=" and URL = ?";
            $parameters[] = $url;
        }
        if($fromDate){
            $where.=" and Date >= ?";
            $parameters[] = $fromDate . ' 00:00:00';
        }
        if($toDate){
            $where.=" and Date <= ?";
            $parameters[] = $toDate . ' 23:59:59';
        }

        $urls = DB::table('dev_errorlog')->select('URL')->distinct()->get();
        $errorLogs = DB::table('dev_errorlog')->whereRaw("$where",$parameters)->select('Id','Description','Code','Message','File','LineNo','URL','Date')->orderBy('Date','DESC')->paginate($perPage);
        return view('sysadmin.bugindex',['errorLogs'=>$errorLogs,'perPage'=>$perPage,'urls'=>$urls]);
    }
    public function getForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $update = false;
        $category = [new Category];
        if($id){
            $update = true;
            $category = Category::find($id);
            if(!$category){
                abort(404);
            }
        }
        return view('application.categoryform',['category'=>$category,'update'=>$update]);
    }
    public function postSave(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        //FORM VALIDATION
        $this->validate($request,
            [
                'Name' => 'required',
            ],
            [
                'Name.required'=>'Please type a Category Name'
            ]
        );
        //END
        $inputs = $request->input();
        $save = false;
        DB::beginTransaction();
        try{
            if($inputs['Id']){
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = Category::find($inputs['Id']);
                $object->fill($inputs);
                $object->update();
            }else{
                $inputs['CreatedBy'] = Auth::id();
                unset($inputs['Id']);
                $save  = true;
                Category::create($inputs);
            }
        }catch(\Exception $e){
            DB::rollBack();
            return back()->with('errormessage',$e->getMessage());
        }

        DB::commit();
        return redirect('categoryindex')->with('successmessage','Record has been '. ($save?'saved':'updated').'!');
    }

    public function getDelete($id): \Illuminate\Http\RedirectResponse
    {
        try{
            Category::where('Id',$id)->delete();
        }catch(\Exception $e){
            return back()->with('errormessage',"Category could not be deleted because there are files under this category.");
        }
        return back()->with('successmessage','Record has been deleted');
    }

    public function fetchDetail(Request $request): \Illuminate\Http\JsonResponse
    {
        $id = $request->input('id');
        $detail = DB::table('dev_errorlog')->where('Id',$id)->pluck('Description');
        if($detail){
            return response()->json(['detail'=>$detail[0]]);
        }else{
            return response()->json(['detail'=>'']);
        }
    }
}
