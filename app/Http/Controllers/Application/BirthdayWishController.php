<?php


namespace App\Http\Controllers\Application;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BirthdayWishController extends Controller
{
    public function getIndex(){
        $message = "Happiness increases when we share; hope you receive a lot on this beautiful day and make you shine more. Happy Birthday!";
        $curDayMonth = date('-m-d');
        $employeesWithBirthday = DB::select("select T1.Id,T1.MobileNo, T1.Email from mas_employee T1 where T1.DateOfBirth LIKE ? and T1.RoleId <> 1 and Status = 1",["%$curDayMonth"]);
        foreach($employeesWithBirthday as $employee):
            $emailSuccess = 0;
            $mobileSuccess = 0;

            $mobileNo = $employee->MobileNo;
            $email = $employee->Email;

            if($mobileNo){
                $mobileSuccess = 1;
                $this->sendSMSTashiCell($mobileNo,$message);
            }else{
                $mobileNo = NULL;
            }
            if($email && $email != "@tashicell.com"){
                $emailSuccess = 1;
                $this->sendMailAlternate($email,$message,"Happy Birthday!");
            }else{
                $email = NULL;
            }
            DB::insert("INSERT INTO sys_birthdaywishlog (EmployeeId, MobileNo, Email, MobileSuccess, EmailSuccess) VALUES (?,?,?,?,?)",[$employee->Id,$mobileNo,$email,$mobileSuccess, $emailSuccess]);
        endforeach;
    }
}
