<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog as ErrorLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct()
    {
        $date = date_create("2020-01-10");
    }

    public function getGradeStep($stepId)
    {
        $gradeStep = DB::select("SELECT Id, PayScale FROM mas_gradestep WHERE STATUS = 1 AND Id = ? ", [$stepId]);
        if ((bool) $gradeStep) {
            return $gradeStep;
        }
    }

    public function fetchActiveDepartments($condition = false, $parameters = []): array
    {
        return DB::select("select Id, Name, ShortName from mas_department where Status = 1 $condition order by Name", $parameters);
    }
    public function getDepartmentOfEmployee($empId)
    {
        $departmentId = DB::select("select T1.DepartmentId from mas_employee T1 where T1.Id = ?", [$empId]);
        if ($departmentId) {
            return $departmentId[0]->DepartmentId;
        }
    }
    public function getDepartmentEmployees($deptId, $excludeSelf = false, $json = false): \Illuminate\Http\JsonResponse|array
    {
        $parameters = [$deptId];
        $condition = " and 1=1";
        if ($excludeSelf) {
            $condition .= " and T1.Id <> ?";
            $parameters[] = Auth::id();
        }
        $employees = DB::select("select T1.Id,T1.Name, T2.Name as Designation,T1.CIDNo, T1.EmpId from mas_employee T1 left join mas_designation T2 on T2.Id = T1.DesignationId where T1.DepartmentId = ? and T1.RoleId <> 1 and coalesce(T1.Status,0) = 1$condition order by T1.Name", $parameters);
        if ($json) {
            return response()->json($employees);
        }
        return $employees;
    }
    public function getAllEmployees($condition = false, $parameters = []): array
    {
        return DB::select("select T1.Id,T1.Name, T2.Name as Designation,T1.EmpId, A.Name as Department from mas_employee T1 join mas_department A on A.Id = T1.DepartmentId join mas_designation T2 on T2.Id = T1.DesignationId where coalesce(T1.Status,0) = 1 and T1.RoleId <> 1 $condition order by T1.Name", $parameters);
    }
    public function getSectionEmployees($sectionId, $excludeSelf = false, $json = false): \Illuminate\Http\JsonResponse|array
    {
        $parameters = [$sectionId];
        $condition = " and 1=1";
        if ($excludeSelf) {
            $condition .= " and T1.Id <> ?";
            $parameters[] = Auth::id();
        }
        $employees = DB::select("select T1.Id,T1.Name, T2.Name as Designation from mas_employee T1 left join mas_designation T2 on T2.Id = T1.DesignationId where T1.SectionId = ? and T1.RoleId <> 1 and coalesce(T1.Status,0) = 1$condition order by T1.Name", $parameters);
        if ($json) {
            return response()->json($employees);
        }
        return $employees;
    }
    public function getDepartmentEmployeesAndMD($deptId, $excludeSelf = false, $selfId = null): array
    {
        $parameters = [$deptId];
        $condition = " and 1=1";
        if ($excludeSelf) {
            $condition .= " and T1.Id <> ?";
            $parameters[] = $selfId;
        }
        $parameters[] = CONST_POSITION_MD;
        return DB::select("select T1.Id,T1.Name, O.Name as Designation from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId where coalesce(T1.Status,0) = 1 and T1.DepartmentId = ? $condition union select T1.Id,T1.Name, O.Name as Designation from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId left join mas_designation T2 on T2.Id = T1.DesignationId where T1.PositionId = ? order by Name", $parameters);
    }
    public function testMail()
    {
        $mailBody = "<p>TEST MAIL!</p>";
        $this->sendMail('sangay.wangdi.moktan@gmail.com', $mailBody, "TEST_" . date('Y-m-d H:i:s'));
    }
    public function sendMail($recipientAddress, $mailBody, $subject, $ccAddresses = null, $bccAddresses = null, $attachment = null)
    {
        return true;
        $env = (config('app.env'));
        Mail::send('emails.email', ['mailBody' => $mailBody], function ($mail) use ($recipientAddress, $ccAddresses, $subject, $bccAddresses, $env, $attachment) {
            //        Mail::queue('emails.email',['mailBody'=>$mailBody],function($mail) use ($recipientAddress,$ccAddresses,$subject,$bccAddresses,$env,$attachment){
            $this->extracted($mail, $subject, $env, $recipientAddress, $ccAddresses, $bccAddresses);
            if ($attachment):
                $mail->attach($attachment);
            endif;
            $mail->from('pmstashicell@gmail.com', 'Online PMS');
        });
    }
    public function sendMailAlternate($recipientAddress, $mailBody, $subject, $ccAddresses = null, $bccAddresses = null)
    {
        $env = (config('app.env'));
        Mail::queue('emails.emailalternate', ['mailBody' => $mailBody], function ($mail) use ($recipientAddress, $ccAddresses, $subject, $bccAddresses, $env) {
            $this->extracted($mail, $subject, $env, $recipientAddress, $ccAddresses, $bccAddresses);
            $mail->from('info@tashicell.com', 'TashiCell');
        });
    }
    public function testSMS()
    {
        $this->sendSMS('97517582331', 'SMS Test');
    }
    function sendSMS($mobile, $message): int
    {
        return 1;
        $env = (config('app.env'));
        //        if($env == 'local'){
        //            $mobile = "97577116699";
        //        }else{
        if (!str_starts_with($mobile, '975')) {
            $mobile = "975$mobile";
        }
        //        }
        $message = urlencode($message);
        $post_fields = '';

        $postData = array(
            'UserName' => CONST_USER,
            'PassWord' => CONST_PASS,
            'UserData' => $message,
            'Concatenated' => '0',
            'Mode' => '0',
            'SenderId' => 'OnlinePMS',
            'Deferred' => 'false',
            'Number' => $mobile,
            'Dsr' => 'false'
        );

        return $this->smsCore($postData, $post_fields);
    }
    function sendSMSTashiCell($mobile, $message): int
    {
        $env = (config('app.env'));
        if ($env == 'local') {
            $mobile = "97577116699";
        } else {
            if (!str_starts_with($mobile, '975')) {
                $mobile = "975$mobile";
            }
        }
        $message = urlencode($message);
        $post_fields = '';

        $postData = array(
            'UserName' => CONST_USER,
            'PassWord' => CONST_PASS,
            'UserData' => $message,
            'Concatenated' => '0',
            'Mode' => '0',
            'SenderId' => \config("app.name"),
            'Deferred' => 'false',
            'Number' => $mobile,
            'Dsr' => 'false'
        );

        return $this->smsCore($postData, $post_fields);
    }
    public function updateDates()
    {
        $employees = DB::select("select T1.EmpId, T1.DateOfAppointment, T1.DateOfBirth from employeeraw T1 where (T1.DateOfBirth like ('%JAN%')) or (T1.DateOfBirth like ('%FEB%')) or (T1.DateOfBirth like ('%MAR%')) or (T1.DateOfBirth like ('%APR%')) or (T1.DateOfBirth like ('%MAY%')) or (T1.DateOfBirth like ('%JUN%')) or (T1.DateOfBirth like ('%JUL%')) or (T1.DateOfBirth like ('%AUG%')) or (T1.DateOfBirth like ('%SEP%')) or (T1.DateOfBirth like ('%OCT%')) or (T1.DateOfBirth like ('%NOV%')) or (T1.DateOfBirth like ('%DEC%')) limit 30");
        if (count($employees) == 0) {
            dd('Complete!');
        }
        foreach ($employees as $employee):
            $updateArray = [];
            $empId = $employee->EmpId;
            $dateOfAppointment = $employee->DateOfAppointment;
            $dateOfBirth = $employee->DateOfBirth;

            $dateOfAppointment = $this->parseDate($dateOfAppointment, true);
            $dateOfBirth = $this->parseDate($dateOfBirth);

            if ($dateOfAppointment) {
                $updateArray['DateOfAppointment'] = $dateOfAppointment;
            }
            if ($dateOfBirth) {
                $updateArray['DateOfBirth'] = $dateOfBirth;
            }

            if (!empty($updateArray)) {
                DB::table('employeeraw')->where('EmpId', $empId)->update($updateArray);
            }
        endforeach;
    }
    function parseDate($date, $appointment = false): bool|string
    {
        $date = trim($date);
        $dateArray = explode("-", $date);
        $day = $dateArray[0] ?? '01';
        $month = $dateArray[1] ?? 'JAN';
        $year = $dateArray[2] ?? '1980';

        $month = strtoupper($month);

        switch ($month):
            case "JAN":
                $month = "01";
                break;
            case "FEB":
                $month = "02";
                break;
            case "MAR":
                $month = "03";
                break;
            case "APR":
                $month = "04";
                break;
            case "MAY":
                $month = "05";
                break;
            case "JUN":
                $month = "06";
                break;
            case "JUL":
                $month = "07";
                break;
            case "AUG":
                $month = "08";
                break;
            case "SEP":
                $month = "09";
                break;
            case "OCT":
                $month = "10";
                break;
            case "NOV":
                $month = "11";
                break;
            case "DEC":
                $month = "12";
                break;
            default:
                return false;
        endswitch;

        $day = strlen($day) == 1 ? "0$day" : $day;
        if ($appointment) {
            $year = strlen($year) == 4 ? $year : "20$year";
        } else {
            $year = strlen($year) == 4 ? $year : "19$year";
        }

        return "$year-$month-$day";
    }
    public function pmsPeriodsForReports(): array
    {
        return DB::select("select T1.Id, T1.PMSNumber, T1.StartDate from sys_pmsnumber T1 where T1.PMSNumber > 0 and T1.Status in (2,3) order by T1.PMSNumber");
    }
    public function fetchSections($condition = false, $parameters = []): array
    {
        return DB::select("select T1.Id,T1.Name, T1.DepartmentId from mas_section T1 where coalesce(T1.Status,0) = 1 $condition order by T1.DepartmentId, T1.Name", $parameters);
    }
    public function saveError(\Exception $e, $is404 = false)
    {
        $errorDesc = "Error Code: " . ($is404 ? '404' : $e->getCode());
        $errorDesc .= "<br/>Error Message: " . ($is404 ? 'Page not found' : $e->getMessage());
        $errorDesc .= "<br/>File: " . $e->getFile();
        $errorDesc .= "<br/>Line No.: " . $e->getLine();
        $errorDesc .= "<br/>URL: " . urldecode($_SERVER['REQUEST_URI']);
        $errorDesc .= "<br/>POST VARS: " . arrayToString($_POST);
        $errorDesc .= "<br/>GET VARS: " . arrayToString($_GET);
        $errorDesc .= "<br/>User Id: " . Auth::id() ? Auth::id() : 'guest';
        $errorDesc .= "<br/>Trace: " . $e->getTraceAsString();

        $error['Id'] = UUID();
        $error['File'] = $e->getFile();
        $error['LineNo'] = $e->getLine();
        $error['Description'] = $errorDesc;
        $error['Message'] = ($is404 ? 'Page not found' : $e->getMessage());
        $error['URL'] = urldecode($_SERVER['REQUEST_URI']);
        $error['Code'] = ($is404 ? '404' : $e->getCode());
        $error['Date'] = date('Y-m-d H:i:s');

        if ($error['Code'] != '404') {
            $object = new Controller;
            $mailBody = $errorDesc;
            $object->sendMail('web.mis@tashicell.com', $mailBody, 'PMS Online: Error on ' . date('Y-m-d H:i:s'));
            $object->sendSMS(77116699, 'PMS Online: Error on ' . date('Y-m-d H:i:s'));
            $object->sendSMS(77106699, 'PMS Online: Error on ' . date('Y-m-d H:i:s'));
        }

        ErrorLog::create($error);
    }
    public function saveAuditTrail($tableName, $id, $deleted = 0)
    {
        $record = DB::table($tableName)->where('Id', $id)->get();
        $recordJson = json_encode($record);
        DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", [$tableName, Auth::id(), $deleted, $recordJson]);
    }
    public function fetchCurrentPMSAdjustmentDetails($id): bool|array
    {
        $applicationSubmissionDetails = DB::table('pms_submission')->where('Id', $id)->selectRaw("DATE_FORMAT(SubmissionTime,'%Y-%m-%d') as SubmittedAt")->pluck('SubmittedAt');
        $submissionTime = $applicationSubmissionDetails[0];
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', $submissionTime)->orderBy('StartDate', 'DESC')->get(['TargetRevenue', 'AchievedRevenue']);
        $targetRevenue = $currentPMSQuery[0]->TargetRevenue;
        $achievedRevenue = $currentPMSQuery[0]->AchievedRevenue;
        $adjustmentPercentage = DB::table('mas_pmssettings')->orderBy('created_at', 'DESC')->pluck('FinalAdjustmentPercent');
        $adjustmentPercentage = isset($adjustmentPercentage[0]) ? $adjustmentPercentage[0] : false;

        if (!$targetRevenue || !$achievedRevenue || !$adjustmentPercentage) {
            return false;
        } else {
            return ['Adjustment' => $adjustmentPercentage, 'ScoreToInject' => round(($achievedRevenue / $targetRevenue * $adjustmentPercentage), 2)];
        }
    }
    function in_arrayi($needle, $haystack): bool
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }
    public function officeSuiteDashboard(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $userId = Auth::id();
        return redirect("https://office-suite.tashicell.com/redirectdashboard?uid=12" . $userId . "88");
    }

    /**
     * @param array $postData
     * @param string $post_fields
     * @return int
     */
    public function smsCore(array $postData, string $post_fields): int
    {
        foreach ($postData as $key => $value) {
            $post_fields .= $key . '=' . $value . '&';
        }
        rtrim($post_fields, '&');

        $url = "http://118.103.137.224:80/cgi-bin/BMP_SendTextMsg?";
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData
            )
        );

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        $output = curl_exec($ch);
        $return = 1;
        if (curl_errno($ch)) {
            $return = 0;
        }
        curl_close($ch);
        return $return;
    }
    public function insertScores()
    {
        $positionDepartmentRatingIds = DB::select("SELECT * FROM mas_positiondepartmentrating WHERE PositionDepartmentId IN (SELECT PositionDepartmentId FROM idforcriteria)");
        foreach ($positionDepartmentRatingIds as $positionDepartmentRatingId):
            $id = $positionDepartmentRatingId->Id;
            DB::insert("INSERT INTO `mas_positiondepartmentratingcriteria` (`Id`, `PositionDepartmentRatingId`, `Description`, `Weightage`, `ApplicableToLevel2`, `CreatedBy`, `EditedBy`, `created_at`, `updated_at`)
VALUES (UUID(), ?, 'Work Achievement', 80.00, 0, 128, NULL, NOW(), NULL);", [$id]);
            DB::insert("INSERT INTO `mas_positiondepartmentratingcriteria` (`Id`, `PositionDepartmentRatingId`, `Description`, `Weightage`, `ApplicableToLevel2`, `CreatedBy`, `EditedBy`, `created_at`, `updated_at`)
VALUES (UUID(), ?, 'Integrity & Honesty', 4.00, 1, 128, NULL, NOW(), NULL);", [$id]);
            DB::insert("INSERT INTO `mas_positiondepartmentratingcriteria` (`Id`, `PositionDepartmentRatingId`, `Description`, `Weightage`, `ApplicableToLevel2`, `CreatedBy`, `EditedBy`, `created_at`, `updated_at`)
VALUES (UUID(), ?, 'Managerial & Leadership Skills', 4.00, 1, 128, NULL, NOW(), NULL);", [$id]);
            DB::insert("INSERT INTO `mas_positiondepartmentratingcriteria` (`Id`, `PositionDepartmentRatingId`, `Description`, `Weightage`, `ApplicableToLevel2`, `CreatedBy`, `EditedBy`, `created_at`, `updated_at`)
VALUES (UUID(), ?, 'Accountability', 3.00, 1, 128, NULL, NOW(), NULL); ", [$id]);
            DB::insert("INSERT INTO `mas_positiondepartmentratingcriteria` (`Id`, `PositionDepartmentRatingId`, `Description`, `Weightage`, `ApplicableToLevel2`, `CreatedBy`, `EditedBy`, `created_at`, `updated_at`)
VALUES (UUID(), ?, 'Communication & Relationship (Internal & external)', 3.00, 1, 128, NULL, NOW(), NULL);", [$id]);
            DB::insert("INSERT INTO `mas_positiondepartmentratingcriteria` (`Id`, `PositionDepartmentRatingId`, `Description`, `Weightage`, `ApplicableToLevel2`,  `CreatedBy`, `EditedBy`, `created_at`, `updated_at`)
VALUES (UUID(), ?, 'Initiative & Creativity', 3.00, 1, 128, NULL, NOW(), NULL); ", [$id]);
            DB::insert("INSERT INTO `mas_positiondepartmentratingcriteria` (`Id`, `PositionDepartmentRatingId`, `Description`, `Weightage`, `ApplicableToLevel2`,  `CreatedBy`, `EditedBy`, `created_at`, `updated_at`)
VALUES (UUID(), ?, 'Problem solving & Decision making', 3.00, 1, 128, NULL, NOW(), NULL);", [$id]);
        endforeach;
    }
    public function assignSupervisorId()
    {
        //Finance Dept
        //--> Grade 5 & 6
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (5,6))")
            ->whereIn("DepartmentId", [1])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 3]);

        //-> Grade 7 & 8
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (7,8))")
            ->whereIn("DepartmentId", [1])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 4]);

        //-> Grade 9 & 10
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (9,10))")
            ->whereIn("DepartmentId", [1])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 5]);

        //-> Grade 11 & 12
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (11,12))")
            ->whereIn("DepartmentId", [1])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 6]);

        //Marketing Department
        //-> Grade 6 to 12
        //        $employees = DB::table("mas_employee")
        //            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (6,7,8,9,10,11,12))")
        //            ->whereIn("SectionId",[9])
        //            ->pluck("Id")->toArray();
        //        DB::table("mas_employee")->whereIn("Id",$employees)->update(['SupervisorId'=>7]);

        //HRAD
        //-> Grade 6,7,8
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (6,7,8))")
            ->whereIn("DepartmentId", [2])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 8]);

        //-> Grade 9 & 10
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (9,10))")
            ->whereIn("DepartmentId", [2])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 9]);

        //-> Grade 11,12
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (11,12))")
            ->whereIn("DepartmentId", [2])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 10]);

        //Plant Operations
        //-> Grade 5,6
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (5,6))")
            ->whereIn("DepartmentId", [3])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 12]);

        //-> Grade 7,8
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (7,8))")
            ->whereIn("DepartmentId", [3])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 13]);

        //-> Grade 9,10,11,12
        $employees = DB::table("mas_employee")
            ->whereRaw("GradeStepId in (select Id from mas_gradestep where Id in (9,10,11,12))")
            ->whereIn("DepartmentId", [3])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 14]);



        //DRIVERS HRAD - LAST
        $employees = DB::table("mas_employee")
            ->whereIn("DesignationId", [9, 26, 30])
            ->whereIn("DepartmentId", [2])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 11]);


        //HODs
        $employees = DB::table("mas_employee")
            ->whereIn("EmpId", [1254, 1693, 1114, 1235, 1226, 1300])
            ->pluck("Id")->toArray();
        DB::table("mas_employee")->whereIn("Id", $employees)->update(['SupervisorId' => 2]);
    }
    public function assignPositionId()
    {
        $employees = DB::table("mas_employee")->get(['Id', 'SupervisorId', 'DepartmentId', 'GradeStepId', 'EmpId']);
        foreach ($employees as $employee):
            $id = $employee->Id;
            $supervisorId = $employee->SupervisorId;
            $gradeStepId = $employee->GradeStepId;
            $positionId = DB::table("mas_position")->where("GradeStepId", $gradeStepId)->where('SupervisorId', $supervisorId)->value("Id");
            if ($positionId) {
                DB::table("mas_employee")->where("Id", $id)->update(['PositionId' => $positionId]);
            }

        endforeach;
        echo "done";
    }
    public function updateCriteria($supervisorId)
    {
        $employees = DB::table("mas_employee")->where('SupervisorId', $supervisorId)->get(['Id', 'SupervisorId', 'DepartmentId', 'GradeStepId']);
        foreach ($employees as $employee):
            $id = $employee->Id;
            $positionId = DB::table("mas_position")->where('SupervisorId', $supervisorId)->value("Id");
            DB::table("mas_employee")->where("Id", $id)->update(['PositionId' => $positionId]);
        endforeach;
        echo "done";
    }

    /**
     * @param $mail
     * @param $subject
     * @param mixed $env
     * @param $recipientAddress
     * @param $ccAddresses
     * @param $bccAddresses
     * @return void
     */
    public function extracted($mail, $subject, mixed $env, $recipientAddress, $ccAddresses, $bccAddresses): void
    {
        $mail->subject($subject);
        if ($env == 'local') {
            $mail->to('web.mis@tashicell.com');
        } else {
            $mail->to($recipientAddress);
        }
        if ($ccAddresses):
            foreach ($ccAddresses as $ccAddress):
                if ($env == 'local') {
                    $mail->cc('web.mis@tashicell.com');
                } else {
                    $mail->cc($ccAddress);
                }
            endforeach;
        endif;
        if ($bccAddresses):
            foreach ($bccAddresses as $bccAddress):
                if ($env == 'local') {
                    $mail->bcc('web.mis@tashicell.com');
                } else {
                    $mail->bcc($bccAddress);
                }
            endforeach;
        endif;
    }

    function getCurrentRound()
    {
        $today = strtotime(date('Y-m-d'));
        $withinFirstPMSOfYear = false;
        $withinSecondPMSOfYear = false;
        $notWithinPMSPeriod = false;

        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-09-31'))) {
            $withinSecondPMSOfYear = true;
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-06-31'))) {
                $withinFirstPMSOfYear = true;
            }
        }
        if (!$withinFirstPMSOfYear && !$withinSecondPMSOfYear) {
            $notWithinPMSPeriod = true;
        }

        if ($notWithinPMSPeriod) {
            $nextPMSId = DB::table("sys_pmsnumber")
                ->where('StartDate', '>=', date('Y-m-d'))
                ->orderBy('StartDate')
                ->take(1)
                ->value("Id");
        } else {
            $nextPMSId = DB::table('sys_pmsnumber')->where('StartDate', "<=", date('Y-m-d'))->orderBy('StartDate', 'DESC')->take(1)->value('Id');
        }
        return $nextPMSId;
    }
}
