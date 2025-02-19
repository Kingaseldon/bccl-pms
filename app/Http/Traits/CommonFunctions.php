<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

trait CommonFunctions
{
    /**
     * @param $empId
     * @return \Illuminate\Support\Collection
     * DESC: Return PMS History of Employee
     */
    function getEmployeePMSHistory($empId): \Illuminate\Support\Collection
    {
        return DB::table('pms_historical as T1')
            ->join('sys_pmsnumber as T2','T2.Id','=','T1.PMSNumberId')
            ->leftJoin('pms_submission as T3','T3.Id','=','T1.PMSSubmissionId')
            ->orderBy('T2.PMSNumber')
            ->where('T1.EmpId',trim($empId))
            ->get(array('T2.PMSNumber','T2.StartDate','T1.PMSSubmissionId','T3.OfficeOrderPath','T1.PMSScore','T1.PMSResult','T1.PMSRemarks'));
    }

    /**
     * @param $submissionId
     * @return string
     * DESC: Return PMS Details with Remarks of Employee
     */
    function getPMSDetailsWithRemarks($submissionId): string
    {
        $history = DB::select("select GROUP_CONCAT(CONCAT('<strong><em>',DATE_FORMAT(A.StatusUpdateTime,'%D %M, %Y %l:%i %p'),':</strong></em> Status changed to <strong><em>',B.Name,'</strong></em>', ' by <strong><em>',C.Name,'</strong></em>', case when A.Remarks is not null and B.Id <> ? and A.Remarks <> '' then concat('<br/><em>',A.Remarks,'</em>') else '' end) order by A.StatusUpdateTime SEPARATOR '<br/><br/>') as History from pms_submissionhistory A join mas_pmsstatus B on A.PMSStatusId = B.Id join mas_employee C on C.Id = A.StatusByEmployeeId where A.SubmissionId = ?",[CONST_PMSSTATUS_DRAFT,$submissionId]);
        return $history[0]->History ?? '';
    }


    /**
     * @param $pmsId
     * @param $employeeId
     * @param string $column
     * @return float
     * DESC: Fetch Goal Achievement Score based on PMS Round ID for Employee
     */
    function getGoalAchievementScore($pmsId, $employeeId, string $column = "T2.SelfScore"): float
    {
        return DB::table("pms_employeegoal as T1")
            ->join('pms_employeegoaldetail as T2','T2.EmployeeGoalId','=','T1.Id')
            ->where('T1.SysPmsNumberId',$pmsId)
            ->where('T1.EmployeeId',$employeeId)
            ->sum(DB::raw($column));
    }

    function getGoalAchievementScoreWeighted($pmsId, $submissionId,$employeeId, string $column = "T2.SelfScore"): float
    {
        $weightageForQuantitative = DB::table("pms_submissiondetail")->where("SubmissionId",$submissionId)->whereRaw("ApplicableToLevel2=0")->value("Weightage");
        return DB::table("pms_employeegoal as T1")
            ->join("pms_employeegoaldetail as T2","T2.EmployeeGoalId",'=','T1.Id')
            ->where("T1.SysPmsNumberId",$pmsId)
            ->where('T1.EmployeeId',$employeeId)
            ->selectRaw("ROUND(coalesce((SUM($column)/100 * $weightageForQuantitative),0),2) as TotalScore")
            ->value("TotalScore");
    }

    function getTotalQualitativeScore($submissionId,$appraiserId,$column,$multiple=false){
        if($multiple){
            return DB::table("pms_submissionmultipledetail as T1")
                ->join("pms_submissionmultiple as T2","T1.SubmissionMultipleId",'=','T2.Id')
                ->join("pms_submissiondetail as T3",'T3.Id','=','T1.SubmissionDetailId')
                ->whereRaw("T3.ApplicableToLevel2 = 1 and T2.SubmissionId = ? and T2.AppraisedByEmployeeId = ?",[$submissionId,$appraiserId])
                ->sum($column);
        }else{
            return DB::table("pms_submissiondetail as T1")
                ->whereRaw("T1.ApplicableToLevel2 = 1 and T1.SubmissionId = ?",[$submissionId])
                ->sum($column);
        }
    }


    /**
     * @param $employeeId
     * @param $submissionId
     * @param $appraiserId
     * @return array
     * DESC: Get Details of appraiser for particular appraiser, based on SubmissionId or Subordinate EmployeeId
     */
    #[ArrayShape(['appraiserLevel' => "false|int", 'appraiserIndex' => "false|int|string", 'hasMultiple' => "bool", 'scoreColumn' => "false|string",'level1ScoreColumns'=>"array",'level2ScoreColumns'=>'array','multipleType'=>'false|int'])] function getAppraiserDetails($employeeId, $submissionId, $appraiserId): array
    {
        $hasMultipleLevel1 = false;
        $hasMultipleLevel2 = false;
        $appraiserLevel = false; //return
        $appraiserIndex = false; //return
        $hasMultiple = false; //return
        $scoreColumn = false; //return
        $level1ScoreColumns = []; //return
        $level2ScoreColumns = []; //return
        $multipleType = false; //return
        if(!$employeeId){
            $employeeId = DB::table("pms_submission")->where('Id',$submissionId)->value("EmployeeId");
        }
        if($employeeId === $appraiserId){
            return ['appraiserLevel'=>false,'appraiserIndex'=>false,'hasMultiple'=>false,'scoreColumn'=>false,'level1ScoreColumns'=>false,'multipleType'=>false];
        }
        $reportingLevel1EmployeeIds = DB::table('mas_hierarchy')->where('EmployeeId',$employeeId)->whereNotNull('Reportinglevel1EmployeeId')->orderby('ReportingLevel1EmployeeId')->pluck('Reportinglevel1EmployeeId')->toArray();
        $indexOfAppraiser = array_search($appraiserId,$reportingLevel1EmployeeIds);

        $reportingLevel2EmployeeIds = DB::table('mas_hierarchy')->where('EmployeeId',$employeeId)->whereNotNull('Reportinglevel2EmployeeId')->pluck('Reportinglevel2EmployeeId')->toArray();
        if(count($reportingLevel1EmployeeIds)>1){
            $multipleType = 1;
            $hasMultipleLevel1 = true;
            $hasMultiple = true;
            for($i=1; $i<=count($reportingLevel1EmployeeIds); $i++):
                $level1ScoreColumns[] = "Level1_".$i."Score";
            endfor;
        }
        if(count($reportingLevel2EmployeeIds)>1){
            $indexOfAppraiser = array_search($appraiserId,$reportingLevel2EmployeeIds);
            if($indexOfAppraiser !== false){
                $multipleType = 2;
            }
            for($i=1; $i<=count($reportingLevel1EmployeeIds); $i++):
                $level2ScoreColumns[] = "Level2_".$i."Score";
            endfor;
            $hasMultipleLevel2 = true;
            $hasMultiple = true;
        }

        if(in_array($appraiserId,$reportingLevel1EmployeeIds)){
            $appraiserLevel = 1;
            if($hasMultipleLevel1){
                $appraiserIndex = $indexOfAppraiser+1;
                $scoreColumn = "Level1_".($indexOfAppraiser+1)."Score";
            }else{
                $scoreColumn = "Level1Score";
            }
        }else if(in_array($appraiserId,$reportingLevel2EmployeeIds)){
            if($hasMultipleLevel2){
                $appraiserIndex = $indexOfAppraiser+1;
            }
            $appraiserLevel = 2;
            $scoreColumn = "Level2Score";
        }else{
            if(in_array(Auth::user()->RoleId,[1,5])){
                $appraiserLevel = 3;
                $scoreColumn = "HRScore";
            }
        }
        return ['appraiserLevel'=>$appraiserLevel,'appraiserIndex'=>$appraiserIndex,'hasMultiple'=>$hasMultiple,'scoreColumn'=>$scoreColumn,'level1ScoreColumns'=>$level1ScoreColumns,'level2ScoreColumns'=>$level2ScoreColumns,'multipleType'=>$multipleType];
    }
}

