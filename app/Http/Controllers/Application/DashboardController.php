<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018-12-31
 * Time: 12:23 PM
 */

namespace App\Http\Controllers\Application;

use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function getIndex(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $roleId = Auth::user()->RoleId;
        $isAdmin = false;
        if((int)$roleId === 1){
            $isAdmin = true;
        }
        return view('application.dashboard',['isAdmin'=>$isAdmin]);
    }
}
