<?php

namespace App\Http\Controllers\Application;

use App\Http\Traits\CommonFunctions;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;
use App\Models\PMSEmployeeGoal;
use App\Models\PMSEmployeeGoalDetail;
use App\Models\PMSEmployeeGoalHistory;

require_once '../phpspreadsheet/vendor/autoload.php';

class GoalController extends Controller
{
    use CommonFunctions;

    public function getList(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $today = strtotime(date('Y-m-d'));
        $roundsForThisYear = DB::table("sys_pmsnumber")->whereRaw("YEAR(StartDate) = ?", [date('Y')])->orderBy("StartDate")->selectRaw("case when MONTH(StartDate)='07' then concat(YEAR(StartDate),' (H1)') else concat((YEAR(StartDate)-1),' (H2)') end as Round, case when MONTH(StartDate) = '01' then 1 else 0 end as IsSecondRound, Id, StartDate,PMSNumber")->get();
        $first = false;
        if (count($roundsForThisYear) === 0) {
            $first = true;
            $roundsForThisYear = DB::table("sys_pmsnumber")->whereRaw("YEAR(StartDate) = ?", [date('Y') + 1])->orderBy("StartDate")->selectRaw("case when MONTH(StartDate)='07' then concat(YEAR(StartDate),' (H1)') else concat((YEAR(StartDate)-1),' (H2)') end as Round, case when MONTH(StartDate) = '01' then 1 else 0 end as IsSecondRound, Id, StartDate,PMSNumber")->get();
        }
        // unset($roundsForThisYear[1]);
        $currentRound = $roundsForThisYear[0]->Round;
        $currentPMSStartDate = DB::table("sys_pmsnumber")->whereRaw("StartDate <= ?", date("Y-m-d"))->orderBy("StartDate", 'DESC')->take(1)->value("StartDate");
        $currentPMSId = count($roundsForThisYear) == 2 ? $roundsForThisYear[1]->Id : $roundsForThisYear[0]->Id;
        $currentPMSNumber = count($roundsForThisYear) == 2 ? $roundsForThisYear[1]->PMSNumber : $roundsForThisYear[0]->PMSNumber;

        foreach ($roundsForThisYear as $round):
            if ((int) $round->IsSecondRound === 1) {
                $secondRoundOfYear = $round->Id;
            } else {
                $firstRoundOfYear = $round->Id;
            }
        endforeach;

        $firstRoundOfYear = $firstRoundOfYear ?? 'zz';
        $secondRoundOfYear = $secondRoundOfYear ?? 'zz';

        $userPositionId = Auth::user()->PositionId;
        if (!in_array($userPositionId, [CONST_POSITION_HOD, CONST_POSITION_HOS, CONST_POSITION_MD])) {
            if (!in_array($userPositionId, [CONST_POSITION_HOD, CONST_POSITION_HOS, CONST_POSITION_MD])) {
                $isAppraiser = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')->whereRaw("(T1.ReportingLevel1EmployeeId = ? or T1.ReportingLevel2EmployeeId = ?) and coalesce(T2.Status,0) = 1", [Auth::id(), Auth::id()])->count();
                if ($isAppraiser > 0) {
                    $userPositionId = CONST_POSITION_HOS;
                }
            }
        }

        $units = [];
        $employees = [];

        if ($userPositionId == CONST_POSITION_HOS) {
            $type = 1;
            $employees = DB::select("select T1.Id, concat(T1.Name,', ',V.Name,' Department', ' (EMP ID: ',T1.EmpId,')') as Name, T2.PMSOutcomeId, T2.Id as PMSSubmissionId,(select C.Id from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal1DefinitionId, (select C.Id from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal2DefinitionId, (select C.Status from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal1DefinitionStatus,(select C.Id from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal2DefinitionStatus,P.Id as GoalDefinitionId,P.Status as GoalDefinitionStatus, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId from (mas_employee T1 join mas_department V on V.Id = T1.DepartmentId join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id) left join pms_employeegoal P on P.EmployeeId = T1.Id and P.SysPmsNumberId = ? join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTime >= ? where coalesce(T1.Status,0) = 1 and (B.ReportingLevel1EmployeeId = ? or B.Reportinglevel2EmployeeId = ?) order by A.DisplayOrder,T1.Name", [$firstRoundOfYear, $secondRoundOfYear, $firstRoundOfYear, $secondRoundOfYear, $currentPMSNumber, $currentPMSStartDate, Auth::id(), Auth::id()]);
        } else if ($userPositionId == CONST_POSITION_HOD) {
            $type = 2;
            $units = DB::select("select distinct T1.Id, concat(T2.Name, ' | ', T1.Name) as Name from mas_section T1 join mas_department T2 on T2.Id = T1.DepartmentId join (mas_employee A join mas_hierarchy B on B.EmployeeId = A.Id) on A.SectionId = T1.Id where B.ReportingLevel1EmployeeId = ? and coalesce(T1.Status,0) = 1 and coalesce(A.Status,0) = 1 order by T1.Name", [Auth::id()]);
            foreach ($units as $section):
                $employees[$section->Id] = DB::select("select T1.Id,(select C.Id from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal1DefinitionId, (select C.Id from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal2DefinitionId, (select C.Status from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal1DefinitionStatus,(select C.Id from pms_employeegoal C where C.EmployeeId = T1.Id and C.SysPmsNumberId = ?) as Goal2DefinitionStatus,'' as Section, P.Id as GoalDefinitionId,P.Status as GoalDefinitionStatus, T1.Name, T2.PMSOutcomeId, T2.Id as PMSSubmissionId, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId from (mas_employee T1 join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id) left join pms_employeegoal P on P.EmployeeId = T1.Id and P.SysPmsNumberId = ? join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTime >= ? where coalesce(T1.Status,0) = 1 and (B.ReportingLevel1EmployeeId = ?) and T1.SectionId = ? order by A.DisplayOrder,T1.Name", [$firstRoundOfYear, $secondRoundOfYear, $firstRoundOfYear, $secondRoundOfYear, $currentPMSNumber, $currentPMSStartDate, Auth::id(), $section->Id]);
            endforeach;
        } else {
            abort(404);
        }

        return view('goals.employeelist')->with('currentRound', $currentRound)->with('firstRoundOfYear', $firstRoundOfYear)->with('secondRoundOfYear', $secondRoundOfYear)->with('currentPMSId', $currentPMSId)->with('type', $type)->with('units', $units)->with('employees', $employees)->with('roundsForThisYear', $roundsForThisYear);
    }

    public function getIndex($id, $round): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        /* PROFILE */
        $data['EmployeeId'] = $id;
        $data['details'] = DB::select("select T1.EmpId,T1.Status,T3.GradeStepId,T1.PositionId,T1.CIDNo,T1.DateOfAppointment as DateOfRegularization, (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel1EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level1Name,
 (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel2EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level2Name, T4.Name as GradeStep, T4.PayScale,
 T1.Extension, T1.MobileNo, T1.DateOfBirth, T1.DateOfAppointment,T1.ProfilePicPath,T1.Extension,T1.Name,B.Name as DesignationLocation,
 T2.Name as Department,A.Name as Section, concat(Z1.Name,case when Z2.Id is null then '' else concat(' - Reporting to ',Z2.Name) end) as
 Position from mas_employee T1 left join mas_designation B on B.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left
 join mas_section A on A.Id = T1.SectionId left join (mas_position T3 join mas_gradestep Z1 on Z1.Id = T3.GradeStepId left join mas_supervisor Z2
 on Z2.Id = T3.SupervisorId) on T3.Id = T1.PositionId left join mas_gradestep T4 on T4.Id = T1.GradeStepId /*left join (mas_hierarchy W1
 join mas_employee W2 on W2.Id = W1.ReportingLevel1EmployeeId left join mas_designation V1 on V1.Id = W2.DesignationId left join
 mas_employee W3 left join mas_designation V2 on V2.Id = W3.DesignationId on W3.Id = W1.ReportingLevel2EmployeeId) on W1.EmployeeId = T1.Id
  */where T1.Id = ?", [$id]);
        if (count($data['details']) == 0) {
            abort(404);
        }
        /* END PROFILE */
        $today = strtotime(date('Y-m-d'));
        $withinFirstPMSOfYear = false;
        $withinSecondPMSOfYear = false;
        $notWithinPMSPeriod = false;

        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-09-31'))) {
            $withinSecondPMSOfYear = true;
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-02-31'))) {
                $withinFirstPMSOfYear = true;
            }
        }
        if (!$withinFirstPMSOfYear && !$withinSecondPMSOfYear) {
            $notWithinPMSPeriod = true;
        }
        $data['isDefined'] = false;
        if ($round) {
            $data['nextPMSId'] = $round;
        } else {
            if ($notWithinPMSPeriod) {
                $data['nextPMSId'] = DB::table("sys_pmsnumber")
                    ->where('StartDate', '>', date('Y-m-d'))
                    ->orderBy('StartDate')
                    ->take(1)
                    ->value("Id");
            } else {
                $data['nextPMSId'] = DB::table('sys_pmsnumber')->where('StartDate', "<=", date('Y-m-d'))->orderBy('StartDate', 'DESC')->take(1)->value('Id');
            }
        }
        $data['goalId'] = DB::table('pms_employeegoal')
            ->where("SysPmsNumberId", $round)
            ->where('EmployeeId', $id)
            ->value("Id");
        $data['goalDetails'] = [new PMSEmployeeGoalDetail()];
        $data['goalSubmissionHistory'] = [new PMSEmployeeGoalHistory()];
        if ($data['goalId']) {
            $data['isDefined'] = true;
            $data['goalDetails'] = DB::table("pms_employeegoaldetail")
                ->where("EmployeeGoalId", $data['goalId'])
                ->where('Type', 2)
                ->orderBy('DisplayOrder')
                ->get(['Id', 'Description', 'DisplayOrder', 'Weightage', 'Target', 'Achievement', 'SelfScore']);
            if (empty($data['goalDetails'])) {
                $data['goalDetails'] = [new PMSEmployeeGoalDetail()];
            }
        }
        return view('goals.index', $data);
    }

    public function postSave(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $id = $request->Id;
        $inputs['EmployeeId'] = $request->EmployeeId;
        $inputs['DepartmentId'] = $request->DepartmentId;
        $inputs['SysPmsNumberId'] = $request->SysPmsNumberId;
        $inputs['Status'] = $request->Status;
        $employee = DB::table('mas_employee')
            ->where('Id', $request->EmployeeId)
            ->selectRaw("concat(Name, ' (',EmpId,')') as Employee")
            ->value("Employee");
        $save = true;
        if ($id) {
            $save = false;
            $inputs['EditedBy'] = Auth::id();
            $inputs['updated_at'] = date("Y-m-d H:i:s");
            $updateObject = PMSEmployeeGoal::find($id);
            $updateObject->fill($inputs);
            $updateObject->update();
        } else {
            $inputs['Id'] = $id = UUID();
            $inputs['CreatedBy'] = Auth::id();
            PMSEmployeeGoal::create($inputs);
        }
        $goalInputs = $request->goaldetailpna;
        DB::beginTransaction();
        try {
            DB::table("pms_employeegoaldetail")->where('EmployeeGoalId', $id)->delete();
            foreach ($goalInputs as $key => $goalInput):
                if ($goalInput['Description'] && $goalInput['Weightage']) {
                    $goalInput['EmployeeGoalId'] = $id;
                    $goalInput['Type'] = 2;
                    $goalInput['Id'] = UUID();
                    $goalInput['CreatedBy'] = Auth::id();
                    PMSEmployeeGoalDetail::create($goalInput);
                }
            endforeach;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect('pmsgoal')->with('errormessage', $e->getMessage());
        }
        DB::commit();

        return redirect('pmsgoal')->with('successmessage', "Goal for $employee has been " . ($save ? "saved" : "updated"));
    }

    public function getMyGoals(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $data['inaccessible'] = true;
        $currentPMSStatus = DB::table('sys_pmsnumber')
            ->where('StartDate', '<=', date('Y-m-d'))
            ->orderBy("StartDate", "DESC")
            ->take(1)
            ->value('Status');
        if ($currentPMSStatus == 1 || $currentPMSStatus == null) {
            $pmsDetails = DB::table('sys_pmsnumber')
                ->where('StartDate', '<=', date('Y-m-d'))
                ->orderBy("StartDate", "DESC")
                ->take(1)
                ->get(['Id', 'StartDate']);
            $nextPMSId = $pmsDetails[0]->Id;
            $currentPMSStartDate = $pmsDetails[0]->StartDate;
            $pmsSubmissionQuery = DB::table("viewpmssubmissionwithlaststatus")
                ->where("EmployeeId", Auth::id())
                ->whereRaw("DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ?", [$currentPMSStartDate])
                ->get(["LastStatusId", "StatusByEmployeeId"]);
            $status = count($pmsSubmissionQuery) > 0 ? $pmsSubmissionQuery[0]->LastStatusId : false;
            $statusByEmployeeId = count($pmsSubmissionQuery) > 0 ? $pmsSubmissionQuery[0]->StatusByEmployeeId : false;
            if (($status == CONST_PMSSTATUS_SENTBACKBYVERIFIER) || ($status == CONST_PMSSTATUS_DRAFT && $statusByEmployeeId == Auth::id())) {
                $data['inaccessible'] = false;
            }
            if (count($pmsSubmissionQuery) == 0) {
                $data['inaccessible'] = false;
            }
        } else {
            $data['inaccessible'] = false;
            $nextPMSId = DB::table("sys_pmsnumber")
                ->where('StartDate', '>=', date('Y-m-d'))
                ->orderBy('StartDate')
                ->take(1)
                ->value("Id");
        }

        $data['goalId'] = DB::table('pms_employeegoal')
            ->where('SysPmsNumberId', $nextPMSId)
            ->where('EmployeeId', Auth::id())
            ->value('Id');
        $data['onmTargets'] = DB::table('pms_employeegoaldetail as T2')
            ->where('T2.EmployeeGoalId', $data['goalId'])
            ->where('T2.Type', 1)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks']);
        $data['goalTargets'] = DB::table('pms_employeegoaldetail as T2')
            ->where('T2.EmployeeGoalId', $data['goalId'])
            ->where('T2.Type', 2)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks']);
        return view('goals.selfgoals', $data);
    }

    public function postSaveScore(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $inputs = $request->input();
        $onmscores = $inputs['goaldetailonm'] ?? [];
        $pnascores = $inputs['goaldetailpna'] ?? [];
        $pmsSubmissionId = $inputs['PMSSubmissionId'] ?? false;
        $employeeId = $inputs['EmployeeId'] ?? false;
        $appraiserDetails = $this->getAppraiserDetails(false, $pmsSubmissionId, Auth::id());
        $level1ScoreColumns = $appraiserDetails['level1ScoreColumns'];
        foreach ($onmscores as $key => $onmscore):
            $id = $onmscore['Id'];
            $updateObject = PMSEmployeeGoalDetail::find($id);
            $updateObject->fill($onmscore);
            $updateObject->update();
        endforeach;
        $level = $appraiserDetails['appraiserLevel'];
        foreach ($pnascores as $key => $pnascore):
            $multipleAppraisersTotal = 0;
            $multipleAppraisersFilled = true;
            $id = $pnascore['Id'];
            $updateObject = PMSEmployeeGoalDetail::find($id);

            if (!$request->has("IsSelf")) {
                if ((int) $level === 1 && $appraiserDetails['hasMultiple'] && ($appraiserDetails['multipleType'] == 1)) {
                    $pnascore[$appraiserDetails['scoreColumn']] = $pnascore['Level1Score'];
                    foreach ($level1ScoreColumns as $level1ScoreColumn):

                        if ($level1ScoreColumn !== $appraiserDetails['scoreColumn']) {
                            if ($updateObject->$level1ScoreColumn === null) {
                                $multipleAppraisersFilled = false;
                            } else {
                                $multipleAppraisersTotal += $updateObject->$level1ScoreColumn;
                            }
                        } else {
                            $multipleAppraisersTotal += $pnascore['Level1Score'];
                        }
                    endforeach;
                    unset($pnascore['Level1Score']);
                    if ($multipleAppraisersFilled) {
                        $pnascore['Level1Score'] = $multipleAppraisersTotal / count($level1ScoreColumns);
                    }
                }
            }

            $updateObject->fill($pnascore);
            $updateObject->update();
        endforeach;

        $employeeGoalId = DB::table("pms_employeegoaldetail")->where("Id", $id)->value("EmployeeGoalId");
        $redirect = "mypmsgoal";
        $message = "Your goal achievements have been recorded";
        if (isset($inputs['Redirect'])) {
            $redirect = $inputs['Redirect'];
            $message = "Your scoring of employee's goal achievements have been saved";
        }
        if (!$request->has("IsSelf") && $level) {
            if ($level === 1) {
                if (($appraiserDetails['hasMultiple'] && $multipleAppraisersFilled) || !$appraiserDetails['hasMultiple']) {
                    DB::update("UPDATE pms_submissiondetail T1 SET T1.Level1Rating = ((select SUM(A.Level1Score) from pms_employeegoaldetail A where A.EmployeeGoalId = ?)/(select sum(A.Weightage) from pms_employeegoaldetail A where A.EmployeeGoalId = ?) * (select A.Weightage from pms_submissiondetail A where A.SubmissionId = ? and A.ApplicableToLevel2=0)) where T1.ApplicableToLevel2 = 0 and T1.SubmissionId = ?", [$employeeGoalId, $employeeGoalId, $pmsSubmissionId, $pmsSubmissionId]);
                }
            }
            if ($level === 2) {
                DB::update("UPDATE pms_submissiondetail T1 SET T1.Level2Rating = ((select SUM(A.Level2Score) from pms_employeegoaldetail A where A.EmployeeGoalId = ?)/(select sum(A.Weightage) from pms_employeegoaldetail A where A.EmployeeGoalId = ?) * (select A.Weightage from pms_submissiondetail A where A.SubmissionId = ? and A.ApplicableToLevel2=0)) where T1.ApplicableToLevel2 = 0 and T1.SubmissionId = ?", [$employeeGoalId, $employeeGoalId, $pmsSubmissionId, $pmsSubmissionId]);
            }
            if ($level === 3) {
                DB::update("UPDATE pms_submissiondetail T1 SET T1.HRRating = ((select SUM(A.HRScore) from pms_employeegoaldetail A where A.EmployeeGoalId = ?)/(select sum(A.Weightage) from pms_employeegoaldetail A where A.EmployeeGoalId = ?) * (select A.Weightage from pms_submissiondetail A where A.SubmissionId = ? and A.ApplicableToLevel2=0)) where T1.ApplicableToLevel2 = 0 and T1.SubmissionId = ?", [$employeeGoalId, $employeeGoalId, $pmsSubmissionId, $pmsSubmissionId]);
            }
        }
        return redirect($redirect)->with('successmessage', $message);
    }

    public function fetchSubordinateGoals(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        $id = $request->id;
        $data['id'] = $id;
        $employeeQuery = DB::table("pms_submission as T1")
            ->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')
            ->where('T1.Id', $id)
            ->get(["T1.EmployeeId", 'T2.Name', 'T2.EmpId']);
        if (count($employeeQuery) === 0) {
            return abort("401");
        }
        $employeeId = $employeeQuery[0]->EmployeeId;
        $appraiserDetails = $this->getAppraiserDetails($employeeId, false, Auth::id());
        $scoreColumn = $appraiserDetails['scoreColumn'];
        $data['Employee'] = $employeeQuery[0]->Name . " (" . $employeeQuery[0]->EmpId . ")";
        $currentPMSStatus = DB::table('sys_pmsnumber')
            ->where('StartDate', '<=', date('Y-m-d'))
            ->orderBy("StartDate", "DESC")
            ->take(1)
            ->value('Status');
        if ($currentPMSStatus == 1 || $currentPMSStatus == null) {
            $pmsDetails = DB::table('sys_pmsnumber')
                ->where('StartDate', '<=', date('Y-m-d'))
                ->orderBy("StartDate", "DESC")
                ->take(1)
                ->get(['Id', 'StartDate']);
            $nextPMSId = $pmsDetails[0]->Id;
        } else {
            $nextPMSId = DB::table("sys_pmsnumber")
                ->where('StartDate', '>=', date('Y-m-d'))
                ->orderBy('StartDate')
                ->take(1)
                ->value("Id");
        }
        $data['goalId'] = DB::table('pms_employeegoal')
            ->where('SysPmsNumberId', $nextPMSId)
            ->where('EmployeeId', $employeeId)
            ->value('Id');
        $data['onmTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 1)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', "T2.$scoreColumn as Level1Score", 'T2.Level1Remarks', 'T2.Level2Remarks']);
        $data['goalTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 2)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', "T2.$scoreColumn as Level1Score", 'T2.Level1Remarks', 'T2.Level2Remarks']);
        return view('goals.subordinategoals', $data);
    }

    public function fetchSubordinateGoalsL2(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        $id = $request->id;
        $data['id'] = $id;
        $employeeQuery = DB::table("pms_submission as T1")
            ->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')
            ->where('T1.Id', $id)
            ->get(["T1.EmployeeId", 'T2.Name', 'T2.EmpId']);
        if (count($employeeQuery) === 0) {
            return abort("401");
        }
        $employeeId = $employeeQuery[0]->EmployeeId;
        $data['Employee'] = $employeeQuery[0]->Name . " (" . $employeeQuery[0]->EmpId . ")";
        $currentPMSStatus = DB::table('sys_pmsnumber')
            ->where('StartDate', '<', date('Y-m-d'))
            ->orderBy("StartDate", "DESC")
            ->take(1)
            ->value('Status');
        if ($currentPMSStatus == 1 || $currentPMSStatus == null) {
            $pmsDetails = DB::table('sys_pmsnumber')
                ->where('StartDate', '<', date('Y-m-d'))
                ->orderBy("StartDate", "DESC")
                ->take(1)
                ->get(['Id', 'StartDate']);
            $nextPMSId = $pmsDetails[0]->Id;
        } else {
            $nextPMSId = DB::table("sys_pmsnumber")
                ->where('StartDate', '>=', date('Y-m-d'))
                ->orderBy('StartDate')
                ->take(1)
                ->value("Id");
        }
        $data['goalId'] = DB::table('pms_employeegoal')
            ->where('SysPmsNumberId', $nextPMSId)
            ->where('EmployeeId', $employeeId)
            ->value('Id');
        $data['onmTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 1)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', 'T2.Level1Score', 'T2.Level1Remarks', 'T2.Level2Remarks']);
        $data['goalTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 2)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', 'T2.Level1Score', 'T2.Level1Remarks', 'T2.Level2Remarks']);
        return view('goals.subordinategoalsl2', $data);
    }

    public function uploadKPIFile(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $data['type'] = $request->input('type');
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file);
        $data['sheetData'] = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        unset($data['sheetData'][0]);
        $data['sheetRowCount'] = count($data['sheetData']);
        $html = view('goals.loadgoalsfromexcel', $data);
        return $html;
    }

    public function correctScores()
    {
        $currentPMSId = $this->getCurrentRound();
        $submissions = DB::table("pms_submission as T1")
            ->join("pms_submissiondetail as T2", "T2.SubmissionId", "=", "T1.Id")
            ->join("mas_employee as T3", "T3.Id", '=', 'T1.EmployeeId')
            //                        ->whereNotNull("T2.Level1Rating")
            ->whereNotNull("T1.PMSOutcomeId")
            ->whereRaw("T2.ApplicableToLevel2=0")
            ->whereIn("T3.EmpId", [617])
            ->get(["T1.Id", "T1.EmployeeId", 'T3.EmpId', "T1.WeightageForLevel1", "T2.Level1Rating"]);

        foreach ($submissions as $submission):
            $submissionId = $submission->Id;
            $empId = $submission->EmpId;
            $finalScore = $this->getFinalScore($submissionId);
            if ($finalScore !== false) {
                DB::table("pms_historical")->where("PMSNumberId", $currentPMSId)->where("EmpId", $empId)->update(["PMSSCore" => $finalScore]);
            }

            //            $employeeId = $submission->EmployeeId;
//            $reportingLevel1EmployeeIds = DB::table('mas_hierarchy')->where('EmployeeId',$employeeId)->whereNotNull('Reportinglevel1EmployeeId')->orderby('ReportingLevel1EmployeeId')->pluck('Reportinglevel1EmployeeId')->toArray();
//            $reportingLevel2EmployeeIds = DB::table('mas_hierarchy')->where('EmployeeId',$employeeId)->whereNotNull('Reportinglevel2EmployeeId')->pluck('Reportinglevel2EmployeeId')->toArray();
//            $hasMultiple = false;
//            if(count($reportingLevel1EmployeeIds)>1 || count($reportingLevel2EmployeeIds)>1){
//                $hasMultiple = true;
//            }
//            if(!$hasMultiple){
//                $qualitativeScore = DB::table("pms_submission as T1")->join("pms_submissiondetail as T2","T2.SubmissionId","=","T1.Id")
//                    ->where("T1.Id",$submissionId)->whereRaw("T2.ApplicableToLevel2=0")->get(["T2.Level1Rating","T2.Weightage"]);
//                $goalScore = $this->getGoalAchievementScore(26,$employeeId,"T2.Level1Score");
//                $level1Score = ($goalScore/100) * $qualitativeScore[0]->Weightage;
//                DB::table("pms_submissiondetail")->where("SubmissionId",$submissionId)->whereRaw("ApplicableToLevel2=0")->update(["Level1Rating"=>$level1Score]);
//            }

        endforeach;
    }

    function getFinalScore($id): float
    {
        $application = DB::select("select T1.Id,T1.NewPayScale,T1.FilePath,T1.File2Path,T1.File3Path,T1.File4Path,T1.NewDesignationId,T1.NewGradeId,T1.NewLocation,T1.NewBasicPay,T1.NewGradeStepId,T1.NewSupervisorId,coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) as PMSOutcomeId, T5.HasBasicPayChange, T5.HasDesignationAndLocationChange, T5.HasPayChange, T5.HasPositionChange, T1.FinalRemarks, T1.OutcomeDateTime,T1.EmployeeId,T1.WeightageForLevel1, T1.Level2CriteriaType,T1.WeightageForLevel2, A.Name as Level1Employee, B.Name as Level2Employee from viewpmssubmissionwithlaststatus T1 join (mas_hierarchy T2 join (mas_employee T3 join mas_position A on A.Id = T3.PositionId) on T2.ReportingLevel1EmployeeId = T3.Id left join (mas_employee T4 join mas_position B on B.Id = T4.PositionId) on T4.Id = T2.ReportingLevel2EmployeeId) on T2.EmployeeId = T1.EmployeeId left join mas_pmsoutcome T5 on T5.Id = coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) where T1.Id = ?", [$id]);

        $applicationDetails = DB::select("select T2.AssessmentArea, T2.ApplicableToLevel2,T2.Weightage, T2.SelfRating, T2.Level1Rating, T2.Level2Rating from viewpmssubmissionwithlaststatus T1 join pms_submissiondetail T2 on T2.SubmissionId = T1.Id where T1.Id = ?", [$id]);
        $finalScore = DB::table('pms_submissionfinalscore')->where('SubmissionId', $id)->pluck('FinalScore');
        if (count($finalScore)):
            $finalScore = $finalScore[0];
        else:
            $finalScore = '';
        endif;

        $appraisalType = '';
        if (count($application) === 0) {
            return false;
        }
        if ($application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0):
            if ($application[0]->Level2CriteriaType == 2):
                $type = 1;
            else:
                $type = 2;
            endif;
        else:
            $type = 3;
        endif;

        $finalAdjustmentPercentDetails = $this->fetchCurrentPMSAdjustmentDetails($application[0]->Id);

        $count = 1;
        $level2WeightedTotal = $selfRatingTotal = $level1QualitativeTotal = $level1QuantitativeTotal = $level2QualitativeTotal = $level2QuantitativeTotal = $level1RatingTotal = $level2RatingTotal = $qualitativeWeightageTotal = $quantitativeWeightageTotal = 0;
        foreach ($applicationDetails as $assessmentArea):
            $selfRatingTotal += $assessmentArea->SelfRating;
            if ($assessmentArea->ApplicableToLevel2 == 0):
                $quantitativeWeightageTotal += $assessmentArea->Weightage;
                $level1QuantitativeTotal += $assessmentArea->Level1Rating;
            else:
                $qualitativeWeightageTotal += $assessmentArea->Weightage;
                $level1QualitativeTotal += $assessmentArea->Level1Rating;
            endif;

            if ($application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0):
                if ($assessmentArea->ApplicableToLevel2 == 0):
                    $level2QuantitativeTotal += $assessmentArea->Level2Rating;
                else:
                    $level2QualitativeTotal += $assessmentArea->Level2Rating;
                endif;
            endif;
            $count++;
        endforeach;
        $level1RatingTotal = $level1QualitativeTotal + $level1QuantitativeTotal;
        if ($application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0):
            $level2RatingTotal = $level2QualitativeTotal + $level2QuantitativeTotal;
        endif;
        if ($finalAdjustmentPercentDetails):
            $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal;
        endif;
        $level1WeightedTotal = $level1RatingTotal / 100 * $application[0]->WeightageForLevel1;
        if ($finalAdjustmentPercentDetails):
            $level1AdjustedTotal = round($adjustedLevel1Score, 2) / 100 * $application[0]->WeightageForLevel1;
        endif;
        if ($type == 1):
            if ($finalAdjustmentPercentDetails):
                $adjustedLevel2Score = ($level2QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level2QualitativeTotal;
            endif;
            $level2WeightedTotal = $level2RatingTotal / 100 * $application[0]->WeightageForLevel2;
            if ($finalAdjustmentPercentDetails):
                $level2AdjustedTotal = round($adjustedLevel2Score, 2) / 100 * $application[0]->WeightageForLevel2;
            endif;
            $finalScore = $finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2) + round($level2AdjustedTotal, 2)) : (round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2));
        elseif ($type == 2):
            $level2WeightedTotal = $level2RatingTotal / $qualitativeWeightageTotal * $application[0]->WeightageForLevel2;
            $finalScore = round($finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2) + round($level2WeightedTotal, 2)) : (round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2)), 2);
        else:
            $finalScore = round($finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2)) : (round($level1WeightedTotal, 2)), 2);
        endif;

        return round($finalScore, 2);
    }

}
