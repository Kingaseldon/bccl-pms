<?php


namespace App\Http\Controllers\Application;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class DocumentController extends Controller
{
    #[NoReturn] public function getView(){
        dd('view page');
    }
    public function getIndex(){

    }
    public function getForm($id = null){

    }
    public function postSave(Request $request){

    }
    public function getDelete($id){

    }
}
