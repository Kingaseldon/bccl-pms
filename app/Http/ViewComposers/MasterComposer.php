<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018-12-31
 * Time: 2:41 PM
 */
namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Auth;
use Illuminate\Support\Facades\Request as Request;
use Illuminate\Support\Facades\DB;
class MasterComposer
{
    protected $roleId;
    protected $isAdmin;
    protected $currentRoute;
    protected $isAppraiser;
    protected $isLevel1Appraiser;
    protected $isLevel2Appraiser;
    public function __construct(){
        $this->roleId = Auth::user()->RoleId;
        $this->isAdmin = ($this->roleId == 1)?true:false;
        $this->currentRoute = Request::segment(1);
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

    }
}
