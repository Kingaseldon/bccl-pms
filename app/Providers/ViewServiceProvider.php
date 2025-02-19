<?php


namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ViewServiceProvider extends ServiceProvider
{
    protected $roleId;
    protected $isAdmin;
    protected $currentRoute;
    protected $isAppraiser;
    protected $isLevel1Appraiser;
    protected $isLevel2Appraiser;
    public function register(){

    }
    public function boot(Request $request){
        View::composer('master',function($view) use ($request){
            $this->roleId = Auth::user()->RoleId;
            $this->isAdmin = ($this->roleId == 1)?true:false;
            $this->currentRoute = \Illuminate\Support\Facades\Request::segment(1);
            $this->isAppraiser = false;
            if(!in_array(Auth::user()->PositionId,[CONST_POSITION_HOS,CONST_POSITION_HOD,CONST_POSITION_MD])){
                $isAppraiser = DB::table('mas_hierarchy as T1')->join('mas_employee as T2','T2.Id','=','T1.EmployeeId')->whereRaw("(T1.ReportingLevel1EmployeeId = ? or T1.ReportingLevel2EmployeeId = ?) and coalesce(T2.Status,0) = 1",[Auth::id(),Auth::id()])->count();
                $isLevel1Appraiser = DB::table('mas_hierarchy as T1')->join('mas_employee as T2','T2.Id','=','T1.EmployeeId')->whereRaw("T1.ReportingLevel1EmployeeId = ? and coalesce(T2.Status,0) = 1",[Auth::id()])->count();
                $isLevel2Appraiser = DB::table('mas_hierarchy as T1')->join('mas_employee as T2','T2.Id','=','T1.EmployeeId')->whereRaw("T1.ReportingLevel2EmployeeId = ? and coalesce(T2.Status,0) = 1",[Auth::id()])->count();
                if($isAppraiser > 0){
                    $this->isAppraiser = true;
                }
                if($isLevel1Appraiser > 0){
                    $this->isLevel1Appraiser = true;
                }
                if($isLevel2Appraiser > 0){
                    $this->isLevel2Appraiser = true;
                }
            }
            $view->with('roleId',$this->roleId)->with('isAppraiser',$this->isAppraiser)->with('isLevel1Appraiser',$this->isLevel1Appraiser)->with('isLevel2Appraiser',$this->isLevel2Appraiser)->with('isAdmin',$this->isAdmin)->with('currentRoute',$this->currentRoute);
        });

    }
}
