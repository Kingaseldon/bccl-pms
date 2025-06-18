<?php

/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 * SWM..
 */

namespace App\Http\Controllers\Application;

use App\Http\Traits\CommonFunctions;
use App\Models\PMSSubmission;
use App\Models\PMSSubmissionDetail;
use App\Models\PMSSubmissionHistory;
use App\Models\PMSSubmissionMultiple;
use App\Models\PMSSubmissionMultipleDetail;
use App\Models\PMSHistorical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use PDF; //FOR PDF:: usage
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;

class PMSController extends Controller
{
    use CommonFunctions;

    public function getIndex()
    {
        $scoresOfSubordinate = [];
        $noOfEmployees = 0;
        $today = strtotime(date('Y-m-d'));
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['Id', 'Status', 'PMSNumber', 'StartDate']);
        $status = $statusQuery[0]->Status;
        $currentPMSNumber = $statusQuery[0]->PMSNumber;
        $currentPMSId = $pmsId;
        $currentPMSStartDate = $statusQuery[0]->StartDate;
        if ($status == 2 || $status == 3) {
            $previousPMS = (int) $currentPMSNumber - 1;
            $previousPMSDateQuery = DB::table('sys_pmsnumber')->where('PMSNumber', $previousPMS)->pluck('StartDate');
            $previousPMSDate = $previousPMSDateQuery[0];

            $endingOfCurrentPMS = date_sub(date_create($currentPMSStartDate), date_interval_create_from_date_string("1 Days"));
            $endingDateOfCurrentPMS = $endingOfCurrentPMS->format('Y-m-d');

            $message = "PMS for " . convertDateToClientFormat($previousPMSDate) . " to " . convertDateToClientFormat($endingDateOfCurrentPMS) . " has been closed by HR Admin.";

            return view('application.pmsindex')->with('status', $status)->with('closedMessage', $message);
        }

        $withinFirstPMSOfYear = false;
        $withinSecondPMSOfYear = false;
        $notWithinPMSPeriod = false;
        $alreadySubmitted = false;

        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-09-31'))) {
            $withinSecondPMSOfYear = true;
            $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-07-01'), date('Y-09-31')])->count();
            $lastPMSQuery = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-07-01'), date('Y-09-31')])->pluck('Id');
            $lastPMSId = isset($lastPMSQuery[0]) ? $lastPMSQuery[0] : '';
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-05-31'))) {
                $withinFirstPMSOfYear = true;
                $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-01-01'), date('Y-05-31')])->count();
                $lastPMSQuery = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-01-01'), date('Y-05-31')])->pluck('Id');
                $lastPMSId = isset($lastPMSQuery[0]) ? $lastPMSQuery[0] : '';
            }
        }

        $weightage = [];
        $hasWeightageDefined = true;
        $hasAssessmentAreasDefined = true;
        $assessmentAreas = [];
        $userPositionId = false;
        $userDepartmentId = false;
        $subordinateScorePercentage = false;

        if ($withinFirstPMSOfYear || $withinSecondPMSOfYear) {
            if ($pmsCount > 0) {
                $alreadySubmitted = true;

                $lastStatusQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $lastPMSId)->pluck('LastStatusId');
                if (count($lastStatusQuery) == 0) {
                    abort(404);
                }
                $lastStatusId = $lastStatusQuery[0];

                $lastStatusEmployeeQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $lastPMSId)->pluck('StatusByEmployeeId');
                $lastStatusEmployeeId = $lastStatusEmployeeQuery[0];

                if ($lastStatusId == CONST_PMSSTATUS_DRAFT && $lastStatusEmployeeId == Auth::id()) {
                    return $this->resubmit($lastPMSId, true);
                }
            } else {
                $userPositionId = Auth::user()->PositionId;
                $userDepartmentId = Auth::user()->DepartmentId;

                $userPositionDepartmentIdQuery = DB::table('mas_positiondepartment')->where('DepartmentId', $userDepartmentId)->where('PositionId', $userPositionId)->pluck('Id');
                $userPositionDepartmentId = isset($userPositionDepartmentIdQuery[0]) ? $userPositionDepartmentIdQuery[0] : false;
                if (!$userPositionDepartmentId) {
                    $hasWeightageDefined = false;
                } else {
                    $weightage = DB::select("select WeightageForLevel1, WeightageForLevel2,Level2CriteriaType from mas_positiondepartmentrating where PositionDepartmentId = ?", [$userPositionDepartmentId]);
                    $assessmentAreas = DB::select("select T2.Description,T2.Weightage,T2.ApplicableToLevel2, 0 as DisplayOrder from mas_positiondepartmentrating T1 join mas_positiondepartmentratingcriteria T2 on T2.PositionDepartmentRatingId = T1.Id where T1.PositionDepartmentId = ? and T2.Weightage = (select max(B.Weightage) from mas_positiondepartmentrating A join mas_positiondepartmentratingcriteria B on B.PositionDepartmentRatingId = A.Id where A.PositionDepartmentId = ?) union all select T2.Description,T2.Weightage,T2.ApplicableToLevel2, T2.DisplayOrder from mas_positiondepartmentrating T1 join mas_positiondepartmentratingcriteria T2 on T2.PositionDepartmentRatingId = T1.Id where T1.PositionDepartmentId = ? and T2.Weightage <> (select max(B.Weightage) from mas_positiondepartmentrating A join mas_positiondepartmentratingcriteria B on B.PositionDepartmentRatingId = A.Id where A.PositionDepartmentId = ?) order by DisplayOrder", [$userPositionDepartmentId, $userPositionDepartmentId, $userPositionDepartmentId, $userPositionDepartmentId]);
                    if (count($assessmentAreas) == 0) {
                        $hasAssessmentAreasDefined = false;
                    } else {
                        $goals = DB::table("pms_employeegoal")->where("EmployeeId", Auth::id())->where("SysPmsNumberId", $currentPMSId)->count();
                        if (!$goals) {
                            $hasAssessmentAreasDefined = false;
                        } else {
                            $goalOutOf = $assessmentAreas[0]->Weightage;
                            $noOfEmployeesQuery = DB::select("SELECT count(distinct T1.Id) as NoOfEmployees FROM `pms_submission` T1 join mas_employee T2 on T2.Id = T1.EmployeeId WHERE T1.SubmissionTime >= '2020-01-01' and T2.DepartmentId = ?", [$userDepartmentId]);
                            $noOfEmployees = $noOfEmployeesQuery[0]->NoOfEmployees;
                            if ($userPositionId == CONST_POSITION_HOD) {
                                $scoresOfSubordinate = DB::select("select T1.Name as Section, coalesce((select ROUND(AVG(coalesce(coalesce(A.Level2Rating,A.Level1Rating),A.SelfRating)/A.Weightage * ?),2) from pms_submissiondetail A join pms_submission B on B.Id = A.SubmissionId where B.SectionId = T1.Id and B.EmployeeId <> ? and A.DisplayOrder = 0 and B.SubmissionTime >= ?),0) as Score, (select count(A.Id) from pms_submission A where A.SectionId = T1.Id and A.SubmissionTime >= ?) as SectionEmployeeCount from mas_section T1 where T1.DepartmentId = ? and coalesce(T1.Status,0) = 1", [$goalOutOf, Auth::id(), $currentPMSStartDate, $currentPMSStartDate, $userDepartmentId]);
                            }
                            if ($userPositionId == CONST_POSITION_HOS) {
                                $scoresOfSubordinate = DB::select("select T1.Name as Section, coalesce((select ROUND(AVG(coalesce(A.Level1Rating,A.SelfRating)/A.Weightage * ?),2) from pms_submissiondetail A join pms_submission B on B.Id = A.SubmissionId where B.SectionId = T1.Id and A.DisplayOrder = 0 and B.EmployeeId <> ? and B.SubmissionTime >= ?),0) as Score, (select count(A.Id) from pms_submission A where A.SectionId = T1.Id and A.SubmissionTime >= ?) as SectionEmployeeCount from mas_section T1 where T1.Id = ? and coalesce(T1.Status,0) = 1", [$goalOutOf, Auth::id(), $currentPMSStartDate, $currentPMSStartDate, Auth::user()->SectionId]);
                                $subordinateScorePercentage = DB::table('pms_employeegoal')->where('EmployeeId', Auth::id())->orderBy('created_at', 'DESC')->take(1)->value('SubordinateScorePercentage');
                            }
                        }
                    }
                }
            }
        } else {
            $notWithinPMSPeriod = true;
        }

        $goalAchievementScore = false;
        if ($hasAssessmentAreasDefined) {
            if (true) {
                $goalAchievementScore = DB::table("pms_employeegoal as T1")
                    ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
                    ->where('T1.SysPmsNumberId', $currentPMSId)
                    ->where('T1.EmployeeId', Auth::id())
                    ->sum("T2.SelfScore");

                if ($goalAchievementScore && count($assessmentAreas) > 0) {
                    $goalAchievementScore = ($goalAchievementScore / 100) * $assessmentAreas[0]->Weightage;
                }
            }
        }
        return view('application.pmsindex', ['goalAchievementScore' => $goalAchievementScore, 'noOfEmployees' => $noOfEmployees, 'status' => $status, 'scoresOfSubordinate' => $scoresOfSubordinate, 'subordinateScorePercentage' => $subordinateScorePercentage, 'alreadySubmitted' => $alreadySubmitted, 'hasWeightageDefined' => $hasWeightageDefined, 'hasAssessmentAreasDefined' => $hasAssessmentAreasDefined, 'notWithinPMSPeriod' => $notWithinPMSPeriod, 'assessmentAreas' => $assessmentAreas, 'userPositionId' => $userPositionId, 'userDepartmentId' => $userDepartmentId, 'weightage' => $weightage]);
    }

    public function postUploadExcelApplicant(): \Illuminate\Http\JsonResponse
    {
        $excelFile = Input::file('file');
        $extension = $excelFile->getClientOriginalExtension();
        if (in_array($extension, ['xls', 'xlsx'])) {
            $lastSheetIndex = 'xx';
            global $finalResult;
            $finalResult = 0;
            Excel::load($excelFile, function ($reader) use ($lastSheetIndex, $excelFile) {
                $numberOfSheets = count($reader->all());
                $lastSheetIndex = $numberOfSheets - 1;
                $lastSheet = Excel::selectSheetsByIndex($lastSheetIndex)->load($excelFile);
                global $finalResult;
                $results = $lastSheet->toArray();
                foreach ($results as $key => $result):
                    foreach ($result as $x => $y):
                        $finalResult = $y;
                    endforeach;
                endforeach;
            });
            return response()->json(['success' => true, 'score' => number_format(doubleval($finalResult), 3)]);
        } else {
            return response()->json(['success' => true]);
        }
    }

    public function postSubmitPMS(Request $request): \Illuminate\Http\RedirectResponse
    {
        $today = strtotime(date('Y-m-d'));
        $firstLevelEmailArray = [];
        $firstLevelMobileNoArray = [];

        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-09-31'))) {
            $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-07-01'), date('Y-09-31')])->count();
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-05-31'))) {
                $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-01-01'), date('Y-05-31')])->count();
            }
        }
        if ($pmsCount == 0) {
            $inputs = $request->except(['pmssubmissiondetail', 'File']);
            $file = $request->file('File');
            $file2 = $request->file('File2');
            $details = $request->input('pmssubmissiondetail');

            $extension = $file ? $file->getClientOriginalExtension() : 'xxx';
            $extension2 = $file2 ? $file2->getClientOriginalExtension() : 'xxx';

            if ($file != NULL && !$this->in_arrayi($extension, ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
                return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files or excel or word documents');
            }

            if ($file2 != NULL && !$this->in_arrayi($extension2, ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
                return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files or excel or word documents');
            }

            $directory = 'uploads/' . date('Y') . '/' . date('m');
            if ($file != NULL) {
                $fileName = 'PMS File_' . Auth::user()->EmpId . '_' . randomString() . randomString() . '.' . $file->getClientOriginalExtension();
                $file->move($directory, $fileName);
            } else {
                $fileName = NULL;
            }

            if ($file2 != NULL) {
                $fileName2 = 'PMS Additional Document_' . Auth::user()->EmpId . '_' . randomString() . randomString() . '.' . $file2->getClientOriginalExtension();
                $file2->move($directory, $fileName2);
            }
            DB::beginTransaction();
            try {
                $inputs['Id'] = UUID();
                $inputs['CreatedBy'] = Auth::id();
                $inputs['EmployeeId'] = Auth::id();
                $inputs['DesignationId'] = Auth::user()->DesignationId;
                $inputs['SubmissionTime'] = date('Y-m-d H:i:s');
                if ($file != NULL):
                    $inputs['FilePath'] = $directory . '/' . $fileName;
                endif;
                if ($file2 != NULL) {
                    $inputs['File2Path'] = $directory . '/' . $fileName2;
                }
                $inputs['SectionId'] = Auth::user()->SectionId;
                $empDetails = DB::table('mas_employee as T1')->join('mas_designation as A', 'A.Id', '=', 'T1.DesignationId')->join('mas_gradestep as T2', 'T2.Id', '=', 'T1.GradeStepId')->leftJoin('mas_section as T3', 'T3.Id', '=', 'T1.SectionId')->join('mas_department as T4', 'T4.Id', '=', 'T1.DepartmentId')->where('T1.Id', Auth::id())->get(array("T1.BasicPay", "T2.Name as GradeStep", "T3.Name as Section", "T4.Name as Department", "T2.PayScale", "T1.GradeStepId", "A.Name as Designation"));

                $firstLevelEmployeeIdQuery = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.ReportingLevel1EmployeeId')->where('T1.EmployeeId', Auth::id())->get(['T2.Email', 'T2.MobileNo']);

                if (count($firstLevelEmployeeIdQuery) > 0) {
                    foreach ($firstLevelEmployeeIdQuery as $firstLevelEmployeeId):
                        if ($firstLevelEmployeeId->Email && $firstLevelEmployeeId->Email != '@tashicell.com') {
                            $firstLevelEmailArray[] = $firstLevelEmployeeId->Email;
                        }
                        if ($firstLevelEmployeeId->MobileNo) {
                            $firstLevelMobileNoArray[] = $firstLevelEmployeeId->MobileNo;
                        }
                    endforeach;
                }

                $inputs['BasicPay'] = $empDetails[0]->BasicPay;
                $inputs['PayScale'] = $empDetails[0]->PayScale;
                $inputs['GradeStepId'] = $empDetails[0]->GradeStepId;
                $designation = $empDetails[0]->Designation;
                $department = $empDetails[0]->Department;
                $section = $empDetails[0]->Section;
                PMSSubmission::create($inputs);

                foreach ($details as $detail):
                    $detail['Id'] = UUID();
                    $detail['SubmissionId'] = $inputs['Id'];
                    $detail['CreatedBy'] = Auth::id();
                    PMSSubmissionDetail::create($detail);
                endforeach;

                $this->saveStatus($inputs['Id'], CONST_PMSSTATUS_SUBMITTED, Auth::id());
            } catch (\Exception $e) {
                DB::rollBack();
                $this->saveError($e, false);
                return back()->with('errormessage', 'PMS could not be submitted!');
            }
            DB::commit();

            //SEND SMS AND EMAIL TO NEXT LEVEL WITH LINK
            $redirectLink = url('/') . "?redirect=processpms/" . $inputs['Id'];
            $salutation = Auth::user()->Gender == 'M' ? "Mr. " : "Ms. ";
            $smsMessage = $salutation . Auth::user()->Name . " (" . $designation . ") has submitted PMS. Please check your email for details.";
            $emailMessage = $salutation . Auth::user()->Name . " ($designation) of $section, $department has submitted PMS. <br/><a href='$redirectLink'>Click here to evaluate.</a>";

            if (count($firstLevelEmailArray) > 0) {
                foreach ($firstLevelEmailArray as $firstLevelEmail):
                    $this->sendMail($firstLevelEmail, $emailMessage, $salutation . Auth::user()->Name . " has submitted PMS");
                endforeach;
            }
            if (count($firstLevelMobileNoArray) > 0) {
                foreach ($firstLevelMobileNoArray as $firstLevelMobileNo):
                    $this->sendSMS($firstLevelMobileNo, $smsMessage);
                endforeach;
            }
            //END SEND SMS

            return back()->with('successmessage', 'PMS has been submitted!');
        } else {
            return back()->with('successmessage', 'You have already submitted PMS for this period!');
        }
    }

    public function saveStatus($submissionId, $statusId, $employeeId, $remarks = null): bool
    {
        DB::table('pms_submissionhistory')->insert(['Id' => UUID(), 'Remarks' => $remarks, 'SubmissionId' => $submissionId, 'PMSStatusId' => $statusId, 'StatusByEmployeeId' => $employeeId]);
        return true;
    }

    public function getAppraise(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $isAppraiser = 0;
        $totalEmpCount = '';
        $pmsNotSubmitted = '';
        $pmsNotCompleted = '';
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['Status', 'PMSNumber', 'StartDate']);
        $status = $statusQuery[0]->Status;
        $currentPMSNumber = $statusQuery[0]->PMSNumber;
        $currentPMSStartDate = $statusQuery[0]->StartDate;
        $notWithinPMS = true;

        if ($status == 3) {
            $previousPMS = (int) $currentPMSNumber - 1;
            $previousPMSDateQuery = DB::table('sys_pmsnumber')->where('PMSNumber', $previousPMS)->pluck('StartDate');
            $previousPMSDate = $previousPMSDateQuery[0];
            $endingOfCurrentPMS = date_sub(date_create($currentPMSStartDate), date_interval_create_from_date_string("1 Days"));
            $endingDateOfCurrentPMS = $endingOfCurrentPMS->format('Y-m-d');
            $message = "PMS for " . convertDateToClientFormat($previousPMSDate) . " to " . convertDateToClientFormat($endingDateOfCurrentPMS) . " has been closed by HR Admin.";
            return view('application.pmsappraise')->with('status', $status)->with('closedMessage', $message);
        }

        $today = strtotime(date('Y-m-d'));

        if ($today >= strtotime(date('Y-07-01'))) {
            $finalAdjustmentPercent = DB::table('mas_pmssettings')->whereRaw('created_at >= ?', [date('Y-07-01 00:00:00')])->pluck('FinalAdjustmentPercent');
            $fromDate = date('Y-07-01');
            $toDate = date('Y-09-31');
        } else {
            $finalAdjustmentPercent = DB::table('mas_pmssettings')->whereRaw('created_at >= ? and created_at <= ?', [date('Y-01-01 00:00:00'), date('Y-05-31 23:59:59')])->pluck('FinalAdjustmentPercent');
            $fromDate = date('Y-01-01');
            $toDate = date('Y-05-31');
        }

        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-09-31'))) {
            $notWithinPMS = false;
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-05-31'))) {
                $notWithinPMS = false;
            }
        }

        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $revDetails = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['TargetRevenue', 'AchievedRevenue']);

        $userPositionId = Auth::user()->PositionId;

        $units = [];
        $employees = [];

        if (!in_array($userPositionId, [CONST_POSITION_HOD, CONST_POSITION_HOS, CONST_POSITION_MD])) {
            $isAppraiser = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')->whereRaw("(T1.ReportingLevel1EmployeeId = ? or T1.ReportingLevel2EmployeeId = ?) and coalesce(T2.Status,0) = 1", [Auth::id(), Auth::id()])->count();
            if ($isAppraiser > 0) {
                $userPositionId = CONST_POSITION_HOS;
            }
        }
        if ($userPositionId == CONST_POSITION_HOS) {
            //here for Sr. Chemist
            $type = 1;
            //            $employees = DB::select("select T1.Id,'' as Section,B.ReportingLevel1EmployeeId,B.ReportingLevel2EmployeeId, coalesce(Z1.Status,3) as MultipleStatus,concat(T1.Name,', ',V.Name,
            //' Department (Emp Id: ',T1.EmpId,')') as Name, T1.EmpId, T2.PMSOutcomeId,O.Name as Designation, Z.Name as Position, T2.LastStatusId,
            //T2.StatusByEmployeeId, T2.LastStatusByEmployee, T3.Name as Status, T2.Id as SubmissionId, case when B.ReportingLevel1EmployeeId = ? then 1 else 2 end as Level from
            //(mas_employee T1 join mas_department V on V.Id = T1.DepartmentId join mas_designation O on O.Id = T1.DesignationId join mas_hierarchy B on B.EmployeeId = T1.Id and (B.ReportingLevel1EmployeeId = ? or B.ReportingLevel2EmployeeId = ?) left join
            //(mas_employee D1 join mas_position E1 on E1.Id = D1.PositionId) on D1.Id = B.ReportingLevel1EmployeeId left join (mas_employee D2 join mas_position E2 on E2.Id =
            //D2.PositionId) on D2.Id = B.ReportingLevel2EmployeeId) join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join
            //(viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId left join pms_submissionmultiple Z1 on Z1.SubmissionId = T2.Id and
            //Z1.AppraisedByEmployeeId = ?) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 group by T1.Id order by A.DisplayOrder,T1.Name",[Auth::id(),Auth::id(),Auth::id(),Auth::id(),$fromDate,$toDate]);

            $employees = DB::select("select T1.Id,'' as Section,B.ReportingLevel1EmployeeId,B.ReportingLevel2EmployeeId, coalesce(Z1.Status,3) as MultipleStatus,concat(T1.Name,', ',V.Name,
' Department (Emp Id: ',T1.EmpId,')') as Name, T1.EmpId, T2.PMSOutcomeId,O.Name as Designation, Z.Name as Position, T2.LastStatusId,
T2.StatusByEmployeeId, T2.LastStatusByEmployee, T3.Name as Status, T2.Id as SubmissionId, case when (select count(v1.Id) from mas_hierarchy v1 where v1.EmployeeId = T1.Id and v1.ReportingLevel1EmployeeId = ?) > 0 then 1 else 2 end as Level from
(mas_employee T1 join mas_department V on V.Id = T1.DepartmentId join mas_designation O on O.Id = T1.DesignationId join mas_hierarchy B on B.EmployeeId = T1.Id and (B.ReportingLevel1EmployeeId = ? or B.ReportingLevel2EmployeeId = ?) left join
(mas_employee D1 join mas_position E1 on E1.Id = D1.PositionId) on D1.Id = B.ReportingLevel1EmployeeId left join (mas_employee D2 join mas_position E2 on E2.Id =
D2.PositionId) on D2.Id = B.ReportingLevel2EmployeeId) join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join
(viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId left join pms_submissionmultiple Z1 on Z1.SubmissionId = T2.Id and
Z1.AppraisedByEmployeeId = ?) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 group by T1.Id order by A.DisplayOrder,T1.Name", [Auth::id(), Auth::id(), Auth::id(), Auth::id(), $fromDate, $toDate]);
            //            dd($employees);
        } else if ($userPositionId == CONST_POSITION_HOD) {
            $type = 2;
            $units = DB::select("select distinct T1.Id, T1.Name, T2.Name as Department from mas_section T1 join mas_department T2 on T2.Id = T1.DepartmentId join (mas_employee A join mas_hierarchy B on B.EmployeeId = A.Id) on A.SectionId = T1.Id where B.ReportingLevel1EmployeeId = ? or B.ReportingLevel2EmployeeId = ? and coalesce(T1.Status,0) = 1 and coalesce(A.Status,0) = 1 order by T1.Name", [Auth::id(), Auth::id()]);
            foreach ($units as $section):
                //COMPLETED
                $level1CompletedQuery = DB::select("select count(T1.Id) as CountCompleted from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and ((T1.SectionId = ?) or (T2.SectionId = ?)) and B.ReportingLevel1EmployeeId = ? and T2.LastStatusId in (?,?) order by T1.Name", [$fromDate, $toDate, $section->Id, $section->Id, Auth::id(), CONST_PMSSTATUS_VERIFIED, CONST_PMSSTATUS_APPROVED]);
                $level2CompletedQuery = DB::select("select count(T1.Id) as CountCompleted from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and ((T1.SectionId = ?) or (T2.SectionId = ?)) and B.ReportingLevel2EmployeeId = ? and T2.LastStatusId = ? order by T1.Name", [$fromDate, $toDate, $section->Id, $section->Id, Auth::id(), CONST_PMSSTATUS_APPROVED]);
                $section->Completed = (int) $level1CompletedQuery[0]->CountCompleted + (int) $level2CompletedQuery[0]->CountCompleted; //DB::table('viewpmssubmissionwithlaststatus as T1')->join()

                //PENDING
                $level1PendingQuery = DB::select("select count(T1.Id) as CountPending from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and ((T1.SectionId = ?) or (T2.SectionId = ?)) and B.ReportingLevel1EmployeeId = ? and ((T2.LastStatusId in (?,?)) or (T2.LastStatusId = ? and T2.StatusByEmployeeId = ?)) order by T1.Name", [$fromDate, $toDate, $section->Id, $section->Id, Auth::id(), CONST_PMSSTATUS_SUBMITTED, CONST_PMSSTATUS_SENTBACKBYAPPROVER, CONST_PMSSTATUS_DRAFT, Auth::id()]);
                $level2PendingQuery = DB::select("select count(T1.Id) as CountPending from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and ((T1.SectionId = ?) or (T2.SectionId = ?)) and B.ReportingLevel2EmployeeId = ? and (T2.LastStatusId = ? or (T2.LastStatusId = ? and T2.StatusByEmployeeId = ?)) order by T1.Name", [$fromDate, $toDate, $section->Id, $section->Id, Auth::id(), CONST_PMSSTATUS_VERIFIED, CONST_PMSSTATUS_DRAFT, Auth::id()]);
                $section->Pending = (int) $level1PendingQuery[0]->CountPending + (int) $level2PendingQuery[0]->CountPending;

                //NOT SUBMITTED
                $notSubmittedQuery = DB::select("select sum(1) as CountNotSubmitted from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and ((T1.SectionId = ?) or (T2.SectionId = ?)) and (B.ReportingLevel1EmployeeId = ? or ReportingLevel2EmployeeId = ?) and T2.Id is null and T1.RoleId <> 1 order by T1.Name", [$fromDate, $toDate, $section->Id, $section->Id, Auth::id(), Auth::id()]);
                $section->NotSubmitted = (int) $notSubmittedQuery[0]->CountNotSubmitted;

                $employees[$section->Id] = DB::select("select T1.Id,B.ReportingLevel1EmployeeId,B.ReportingLevel2EmployeeId, coalesce(Z2.Status,3) as MultipleStatus, Z1.Name as Section, T1.Name,T1.EmpId, E1.Name as Level1Position, E2.Name as Level2Position, T2.PMSOutcomeId, O.Name as Designation, A.Name as Position, T2.LastStatusId, T2.StatusByEmployeeId,T2.PMSOutcomeDraft, T2.LastStatusByEmployee, T3.Name as Status, T2.Id as SubmissionId, case when B.ReportingLevel1EmployeeId = ? then 1 else 2 end as Level from (mas_employee T1 join mas_section Z1 on Z1.Id = T1.SectionId join mas_designation O on O.Id = T1.DesignationId join mas_hierarchy B on B.EmployeeId = T1.Id join (mas_employee D1 join mas_position E1 on E1.Id = D1.PositionId) on D1.Id = B.ReportingLevel1EmployeeId left join (mas_employee D2 join mas_position E2 on E2.Id = D2.PositionId) on D2.Id = B.ReportingLevel2EmployeeId) join mas_gradestep A on A.Id = T1.GradeStepId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId left join pms_submissionmultiple Z2 on Z2.SubmissionId = T2.Id and Z2.AppraisedByEmployeeId = ?) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and ((T1.SectionId = ?) or (T2.SectionId = ?)) and (B.ReportingLevel1EmployeeId = ? or ReportingLevel2EmployeeId = ?) order by case when T2.LastStatusId is not null then case when Level = 1 then case when T2.LastStatusId in (?,?) then 1 else case when T2.LastStatusId in (?) then 9999999999999 else 2 end end else case when T2.LastStatusId = ? then 1 else case when T2.LastStatusId = ? then 9999999999999 else 2 end end end else 999999999999999 end, T2.LastStatusId DESC", [Auth::id(), Auth::id(), $fromDate, $toDate, $section->Id, $section->Id, Auth::id(), Auth::id(), CONST_PMSSTATUS_SENTBACKBYAPPROVER, CONST_PMSSTATUS_SUBMITTED, CONST_PMSSTATUS_VERIFIED, CONST_PMSSTATUS_APPROVED, CONST_PMSSTATUS_VERIFIED]);
            endforeach;
        } else {
            $type = 3;
            $units = DB::select("select Id, Name from mas_department where coalesce(Status,0) = 1 order by Name");
            foreach ($units as $department):
                if (Auth::user()->RoleId == 1) {
                    $totalEmpCountQuery = DB::select("select count(distinct T1.Id) as PendingUnsubmittedCount from mas_employee T1 where coalesce(T1.Status,0) = 1 and T1.RoleId <> 1 and coalesce(T1.PositionId,'eeee') <> ?", [CONST_POSITION_MD]);
                    $totalEmpCount = !isset($totalEmpCountQuery[0]) ? 0 : $totalEmpCountQuery[0]->PendingUnsubmittedCount;
                    $pmsNotSubmitted = $this->checkAllPMSSubmitted();
                    $pmsNotCompleted = $this->checkAllPMSCompleted();

                    //COMPLETED
                    $level2CompletedQuery = DB::select("select count(distinct T1.Id) as CountCompleted from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) and T2.LastStatusId = ? and PMSOutcomeId is not null order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, CONST_PMSSTATUS_APPROVED]);
                    $department->Completed = isset($level2CompletedQuery[0]->CountCompleted) ? (int) $level2CompletedQuery[0]->CountCompleted : 0; //DB::table('viewpmssubmissionwithlaststatus as T1')->join()

                    //PENDING
                    $pendingQuery = DB::select("select count(distinct T1.Id) as CountPending from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where (T1.DepartmentId = ? or T2.DepartmentId = ?) and T2.LastStatusId = ? and T2.PMSOutcomeId is null order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, CONST_PMSSTATUS_APPROVED]);
                    $department->Pending = isset($pendingQuery[0]->CountPending) ? (int) $pendingQuery[0]->CountPending : 0;

                    //NOT SUBMITTED
                    $notSubmittedQuery = DB::select("select sum(1) as CountNotSubmitted from mas_employee T1 left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0) = 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) and T2.Id is null and T1.RoleId <> 1 and T1.PositionId <> ? order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, CONST_POSITION_MD]);
                    $department->NotSubmitted = (int) $notSubmittedQuery[0]->CountNotSubmitted;
                    $employees[$department->Id] = DB::select("select T1.Id,B.ReportingLevel1EmployeeId,B.ReportingLevel2EmployeeId,'3' as MultipleStatus,Z1.Name as Section, T2.PMSOutcomeId, T1.Name,T1.EmpId,E1.Name as Level1Position, E2.Name as Level2Position, O.Name as Designation, z.Name as Position, coalesce(H.Name,G.Name) as SavedOutcome,T2.PMSOutcomeDraft, T2.LastStatusId, T2.StatusByEmployeeId, T2.LastStatusByEmployee, T3.Name as Status, T2.Id as SubmissionId, case when B.ReportingLevel1EmployeeId = ? then 1 else 2 end as Level, case when B.ReportingLevel2EmployeeId is null then 1 else 0 end as NoLevel2 from (mas_employee T1 left join mas_section Z1 on Z1.Id = T1.SectionId join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id left join (mas_employee D1 join mas_position E1 on E1.Id = D1.PositionId) on D1.Id = B.ReportingLevel1EmployeeId left join (mas_employee D2 join mas_position E2 on E2.Id = D2.PositionId) on D2.Id = B.ReportingLevel2EmployeeId) join mas_gradestep z on z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId left join mas_pmsoutcome G on G.Id = T2.SavedPMSOutcomeId left join mas_pmsoutcome H on H.Id = T2.PMSOutcomeId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where (T1.DepartmentId = ? or T2.DepartmentId = ?) and coalesce(T1.Status,0) = 1 and T1.PositionId <> ? group by T1.Id order by case when T2.LastStatusId is not null then case when T2.LastStatusId = ? then case when T2.PMSOutcomeId is null then 1 else 3 end else 2 end else 999999999999999 end", [Auth::id(), $fromDate, $toDate, $department->Id, $department->Id, CONST_POSITION_MD, CONST_PMSSTATUS_APPROVED]);
                } else {
                    //COMPLETED
                    $level1CompletedQuery = DB::select("select count(distinct T1.Id) as CountCompleted from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0)= 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) and B.ReportingLevel1EmployeeId = ? and T2.LastStatusId = ? order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, Auth::id(), CONST_PMSSTATUS_APPROVED]);
                    $level2CompletedQuery = DB::select("select count(distinct T1.Id) as CountCompleted from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_gradestep A on A.Id = T1.GradeStepId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0)= 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) and B.ReportingLevel2EmployeeId = ? and T2.LastStatusId = ? order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, Auth::id(), CONST_PMSSTATUS_APPROVED]);
                    $department->Completed = (int) $level1CompletedQuery[0]->CountCompleted + (int) $level2CompletedQuery[0]->CountCompleted; //DB::table('viewpmssubmissionwithlaststatus as T1')->join()

                    //PENDING
                    $level1PendingQuery = DB::select("select count(distinct T1.Id) as CountPending from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0)= 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) and B.ReportingLevel1EmployeeId = ? and (T2.LastStatusId = ? or (T2.LastStatusId = ? and StatusByEmployeeId = ?)) order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, Auth::id(), CONST_PMSSTATUS_SUBMITTED, CONST_PMSSTATUS_DRAFT, Auth::id()]);
                    $level2PendingQuery = DB::select("select count(distinct T1.Id) as CountPending from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0)= 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) and B.ReportingLevel2EmployeeId = ? and (T2.LastStatusId = ? or (T2.LastStatusId = ? and StatusByEmployeeId = ?)) order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, Auth::id(), CONST_PMSSTATUS_VERIFIED, CONST_PMSSTATUS_DRAFT, Auth::id()]);
                    $department->Pending = (int) $level1PendingQuery[0]->CountPending + (int) $level2PendingQuery[0]->CountPending;

                    //NOT SUBMITTED
                    $notSubmittedQuery = DB::select("select count(distinct T1.Id) as CountNotSubmitted from (mas_employee T1 join mas_hierarchy B on B.EmployeeId = T1.Id) join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where coalesce(T1.Status,0)= 1 and (T1.DepartmentId = ? or T2.DepartmentId = ?) and (B.ReportingLevel1EmployeeId = ? or ReportingLevel2EmployeeId = ?) and T2.Id is null order by T1.Name", [$fromDate, $toDate, $department->Id, $department->Id, Auth::id(), Auth::id()]);
                    $department->NotSubmitted = (int) $notSubmittedQuery[0]->CountNotSubmitted;
                    $employees[$department->Id] = DB::select("select T1.Id,B.ReportingLevel1EmployeeId,B.ReportingLevel2EmployeeId,'3' as MultipleStatus,Z1.Name as Section, T2.PMSOutcomeId, T1.Name,T1.EmpId, E1.Name as Level1Position, E2.Name as Level2Position, O.Name as Designation, z.Name as Position, T2.LastStatusId, T2.StatusByEmployeeId, T2.LastStatusByEmployee, T3.Name as Status,T2.PMSOutcomeDraft, T2.Id as SubmissionId, case when B.ReportingLevel1EmployeeId = ? then 1 else 2 end as Level, case when B.ReportingLevel2EmployeeId is null then 1 else 0 end as NoLevel2 from (mas_employee T1 left join mas_section Z1 on Z1.Id = T1.SectionId join mas_designation O on O.Id = T1.DesignationId join mas_hierarchy B on B.EmployeeId = T1.Id join (mas_employee D1 join mas_position E1 on E1.Id = D1.PositionId) on D1.Id = B.ReportingLevel1EmployeeId left join (mas_employee D2 join mas_position E2 on E2.Id = D2.PositionId) on D2.Id = B.ReportingLevel2EmployeeId) join mas_gradestep z on z.Id = T1.GradeStepId join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?) where (T1.DepartmentId = ? or T2.DepartmentId = ?) and (B.ReportingLevel1EmployeeId = ? or ReportingLevel2EmployeeId = ?) and coalesce(T1.Status,0) = 1 order by A.DisplayOrder,T1.Name", [Auth::id(), $fromDate, $toDate, $department->Id, $department->Id, Auth::id(), Auth::id()]);
                }
            endforeach;
        }
        return view('application.pmsappraise')->with('isAppraiser', $isAppraiser)->with('notWithinPMS', $notWithinPMS)->with('totalEmpCount', $totalEmpCount)->with('finalScoreNotApplied', $this->checkAllPMSHasFinalScore())->with('pmsNotSubmitted', $pmsNotSubmitted)->with('pmsNotCompleted', $pmsNotCompleted)->with('revDetails', $revDetails)->with('finalAdjustmentPercent', $finalAdjustmentPercent)->with('type', $type)->with('units', $units)->with('employees', $employees);
    }

    public function getProcess($id): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $pageSettings = ['showSelfScore'];
        $loggedInEmployeeId = Auth::id();
        $hasNoLevel2 = false;
        $hasMultipleLevel1 = false;
        $hasMultipleLevel2 = false;
        $multipleDetailArray = [];

        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['Id', 'Status', 'PMSNumber', 'StartDate']);
        $currentPMSId = $statusQuery[0]->Id;
        $currentPMSStartDate = $statusQuery[0]->StartDate;

        $empDetails = DB::select("select distinct T1.Id,T1.PositionId,T1.DepartmentId,T1.SectionId,T1.EmployeeId,T1.LastStatusId,T1.EmpId,T1.PMSOutcomeId,(select GROUP_CONCAT(CONCAT('<strong><em>',DATE_FORMAT(A.StatusUpdateTime,'%D %M, %Y %l:%i %p'),':</strong></em> Status changed to <strong><em>',B.Name,'</strong></em>', ' by <strong><em>',C.Name,'</strong></em>', case when A.Remarks is not null and B.Id <> ? and A.Remarks <> '' then concat('<br/><em>',A.Remarks,'</em>') else '' end) order by A.StatusUpdateTime SEPARATOR '<br/><br/>') from pms_submissionhistory A join mas_pmsstatus B on A.PMSStatusId = B.Id join mas_employee C on C.Id = A.StatusByEmployeeId where A.SubmissionId = T1.Id) as History,(select GROUP_CONCAT(b1.Name SEPARATOR ',<br/>') from pms_submissionmultiple e1 join mas_employee b1 on b1.Id = e1.AppraisedByEmployeeId where e1.SubmissionId = T1.Id and e1.ForLevel=1) as Level1Employee,T1.CIDNo,T1.OutcomeDateTime,T1.EmployeeId,T1.Level2CriteriaType,T1.WeightageForLevel1,T1.WeightageForLevel2 from viewpmssubmissionwithlaststatus T1 left join (pms_submissionmultiple T2 join mas_employee T3 on T3.Id = T2.AppraisedByEmployeeId) on T2.SubmissionId = T1.Id left join (mas_hierarchy T4 join mas_employee T5 on T4.ReportingLevel1EmployeeId = T5.Id) on T4.EmployeeId = T1.EmployeeId where T1.Id = ? group by T1.Id", [CONST_PMSSTATUS_DRAFT, $id]);
        if (count($empDetails) == 0) {
            abort(404);
        }
        $employeeId = $empDetails[0]->EmployeeId;

        $appraiserDetails = $this->getAppraiserDetails($employeeId, false, Auth::id());
        $scoreColumn = $appraiserDetails['scoreColumn'];

        $reportingLevel1EmployeeIds = DB::table('mas_hierarchy')->where('EmployeeId', $employeeId)->whereNotNull('Reportinglevel1EmployeeId')->pluck('Reportinglevel1EmployeeId')->toArray();

        $reportingLevel2EmployeeIds = DB::table('mas_hierarchy')->where('EmployeeId', $employeeId)->whereNotNull('Reportinglevel2EmployeeId')->pluck('Reportinglevel2EmployeeId')->toArray();
        if (count($reportingLevel1EmployeeIds) > 1) {
            $hasMultipleLevel1 = true;
        }
        if (count($reportingLevel2EmployeeIds) > 1) {
            $hasMultipleLevel2 = true;
        }
        $empId = $empDetails[0]->EmpId;
        if (in_array($loggedInEmployeeId, $reportingLevel1EmployeeIds)) {
            $pageSettings[] = "allowLevel1Input";
            $loggedInLevel = 1;
            if (count(array_filter($reportingLevel2EmployeeIds)) > 0) {
                $type = 1;
            } else {
                $hasNoLevel2 = true;
                $type = 2;
            }
            $append = ',T2.Level1Rating';
        } else if (in_array($loggedInEmployeeId, $reportingLevel2EmployeeIds)) {
            $pageSettings[] = "showLevel1Score";
            $pageSettings[] = "allowLevel2Input";
            $loggedInLevel = 2;
            $type = 2;
            $append = ",T2.Level1Rating";
        } else {
            return redirect('appraisepms')->with('errormessage', 'You do not have  authority to view this record!');
        }

        // getting quantatives for level 1 only and qualitatives for level 2 only
        $userId = Auth::Id();
        $level1UserExist = DB::select('SELECT * FROM mas_hierarchy a WHERE a.EmployeeId = ? AND a.ReportingLevel1EmployeeId = ? ', [$employeeId, $userId]);
        $level2UserExist = DB::select('SELECT * FROM mas_hierarchy a WHERE a.EmployeeId = ? AND a.ReportingLevel2EmployeeId = ? ', [$employeeId, $userId]);

        $pmsDetails = [];
        if ($level1UserExist && $hasNoLevel2) {
            $pmsDetails = DB::select("SELECT T2.Id,T2.AssessmentArea, T3.LastRemarks as Remarks, T3.StatusByEmployeeId, T2.Weightage, coalesce(T2.ApplicableToLevel2,0) as ApplicableToLevel2, T2.SelfRating$append,T2.Level2Rating from pms_submission T1 join pms_submissiondetail T2 on T1.Id = T2.SubmissionId join viewpmssubmissionwithlaststatus T3 on T3.Id = T1.Id where T1.Id = ? order by T2.DisplayOrder", [$id]);
        } else if ($level1UserExist && !$hasNoLevel2) {
            $pmsDetails = DB::select("SELECT T2.Id,T2.AssessmentArea, T3.LastRemarks as Remarks, T3.StatusByEmployeeId, T2.Weightage, coalesce(T2.ApplicableToLevel2,0) as ApplicableToLevel2, T2.SelfRating$append,T2.Level2Rating from pms_submission T1 join pms_submissiondetail T2 on T1.Id = T2.SubmissionId join viewpmssubmissionwithlaststatus T3 on T3.Id = T1.Id where T1.Id = ? and T2.ApplicableToLevel2 = 0 order by T2.DisplayOrder", [$id]);
        } else if ($level2UserExist) {
            $pmsDetails = DB::select("SELECT T2.Id,T2.AssessmentArea, T3.LastRemarks as Remarks, T3.StatusByEmployeeId, T2.Weightage, coalesce(T2.ApplicableToLevel2,0) as ApplicableToLevel2, T2.SelfRating$append,T2.Level2Rating from pms_submission T1 join pms_submissiondetail T2 on T1.Id = T2.SubmissionId join viewpmssubmissionwithlaststatus T3 on T3.Id = T1.Id where T1.Id = ? and T2.ApplicableToLevel2 = 1 order by T2.DisplayOrder", [$id]);
        }

        $qualitativeScoreTotal = false;
        if ($hasMultipleLevel1) {
            $qualitativeScoreTotal = $this->getTotalQualitativeScore($id, Auth::id(), "Score", true);
        }

        $pmsMultiple = DB::select("select T1.FilePath, T1.Remarks, T1.ForLevel, T1.AppraisedByEmployeeId, T2.Name as Appraiser from pms_submissionmultiple T1 join mas_employee T2 on T1.AppraisedByEmployeeId = T2.Id where T1.SubmissionId = ? order by T1.created_at", [$id]);
        $multipleDetails = DB::select("select T1.Score, T1.SubmissionDetailId from pms_submissionmultipledetail T1 where T1.SubmissionMultipleId = (select A.Id from pms_submissionmultiple A where A.SubmissionId = ? and A.AppraisedByEmployeeId = ? and A.Status = 2)", [$id, $loggedInEmployeeId]);

        $multipleLevel1Score = $multipleDetails[0]->MultipleLevel1Score ?? false;

        foreach ($multipleDetails as $multipleDetail):
            $multipleDetailArray[$multipleDetail->SubmissionDetailId] = $multipleDetail->Score;
        endforeach;

        if (!count($pmsDetails)) {
            return redirect('appraisepms')->with('errormessage', 'Record not found');
        }

        $details = DB::select("select T1.Email,T1.MobileNo,T1.Qualification1,T1.Qualification2, T1.ProfilePicPath,T1.Extension,T1.Name,O.Name as DesignationLocation, T2.Name as Department, T4.Name as Section, T3.Name as GradeStep from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left join mas_gradestep T3 on T3.Id = T1.GradeStepId left join mas_section T4 on T4.Id = T1.SectionId where T1.Id = ?", [$employeeId]);

        $files = DB::table('pms_submission')->where('Id', $id)->get(['FilePath', 'File2Path', 'File3Path', 'File4Path']);

        $pmsFile = $files[0]->FilePath;
        $filePath2 = $files[0]->File2Path;
        $filePath3 = $files[0]->File3Path;
        $filePath4 = $files[0]->File4Path;

        $goalAchievementScore = DB::table("pms_employeegoal as T1")
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.SysPmsNumberId', $currentPMSId)
            ->where('T1.EmployeeId', $employeeId)
            ->sum($scoreColumn);

        if ($goalAchievementScore && count($pmsDetails) > 0) {
            $userPositionId = $empDetails[0]->PositionId;
            $userDepartmentId = $empDetails[0]->DepartmentId;

            $userPositionDepartmentIdQuery = DB::table('mas_positiondepartment')->where('DepartmentId', $userDepartmentId)->where('PositionId', $userPositionId)->pluck('Id');
            $userPositionDepartmentId = isset($userPositionDepartmentIdQuery[0]) ? $userPositionDepartmentIdQuery[0] : false;
            $assessmentAreas = DB::select("select T2.Description,T2.Weightage,T2.ApplicableToLevel2, 0 as DisplayOrder from mas_positiondepartmentrating T1 join mas_positiondepartmentratingcriteria T2 on T2.PositionDepartmentRatingId = T1.Id where T1.PositionDepartmentId = ? and T2.Weightage = (select max(B.Weightage) from mas_positiondepartmentrating A join mas_positiondepartmentratingcriteria B on B.PositionDepartmentRatingId = A.Id where A.PositionDepartmentId = ?) union all select T2.Description,T2.Weightage,T2.ApplicableToLevel2, T2.DisplayOrder from mas_positiondepartmentrating T1 join mas_positiondepartmentratingcriteria T2 on T2.PositionDepartmentRatingId = T1.Id where T1.PositionDepartmentId = ? and T2.Weightage <> (select max(B.Weightage) from mas_positiondepartmentrating A join mas_positiondepartmentratingcriteria B on B.PositionDepartmentRatingId = A.Id where A.PositionDepartmentId = ?) order by DisplayOrder", [$userPositionDepartmentId, $userPositionDepartmentId, $userPositionDepartmentId, $userPositionDepartmentId]);

            $goalAchievementScore = ($goalAchievementScore / 100) * $assessmentAreas[0]->Weightage;

            $goalOutOf = $assessmentAreas[0]->Weightage;

            if ($userPositionId == CONST_POSITION_HOS) {
                $scoresOfSubordinate = DB::select("select T1.Name as Section, coalesce((select ROUND(AVG(coalesce(A.Level1Rating,A.SelfRating)/A.Weightage * ?),2) from pms_submissiondetail A join pms_submission B on B.Id = A.SubmissionId where B.SectionId = T1.Id and A.DisplayOrder = 0 and B.EmployeeId <> ? and B.SubmissionTime >= ?),0) as Score, (select count(A.Id) from pms_submission A where A.SectionId = T1.Id and A.SubmissionTime >= ?) as SectionEmployeeCount from mas_section T1 where T1.Id = ?", [$goalOutOf, $empDetails[0]->EmployeeId, $currentPMSStartDate, $currentPMSStartDate, $empDetails[0]->SectionId]);
                $subordinateScorePercentage = DB::table('pms_employeegoal')->where('EmployeeId', $empDetails[0]->EmployeeId)->orderBy('created_at', 'DESC')->take(1)->value('SubordinateScorePercentage');
                $total = 0;
                foreach ($scoresOfSubordinate as $scoreOfSubordinate):
                    $total += $scoreOfSubordinate->Score;
                endforeach;
                $average = $total / count($scoresOfSubordinate);
                $goalAchievementScore = round((($subordinateScorePercentage / 100 * $average) + ((100 - $subordinateScorePercentage) / 100 * $goalAchievementScore)), 2);
            }
        }

        //FETCH HISTORY FOR DISPLAY
        $history = $this->getEmployeePMSHistory(trim($empId));

        return view('application.pmsprocess')->with('goalAchievementScore', $goalAchievementScore)->with('pmsMultiple', $pmsMultiple)->with('loggedInLevel', $loggedInLevel)->with('multipleDetailArray', $multipleDetailArray)->with('hasMultipleLevel1', $hasMultipleLevel1)->with('hasMultipleLevel2', $hasMultipleLevel2)->with('history', $history)->with('filePath4', $filePath4)->with('empDetails', $empDetails)->with('hasNoLevel2', $hasNoLevel2)->with('details', $details)->with('type', $type)->with('id', $id)->with('pmsDetails', $pmsDetails)->with('pmsFile', $pmsFile)->with('filePath2', $filePath2)->with('filePath3', $filePath3)
            ->with('level1UserExist', $level1UserExist)->with('level2UserExist', $level2UserExist);
    }

    public function postProcess(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $inputs = $request->input('pmssubmissiondetail');
        $directory = 'uploads/' . date('Y') . '/' . date('m');

        $type = $request->Type;
        $remarks = $request->Remarks ?: null;
        $file3 = $request->file('File3');
        $file4 = $request->file('File4');
        $secondLevelEmailArray = [];
        $secondLevelMobileNoArray = [];
        $firstLevelEmailArray = [];
        $firstLevelMobileNoArray = [];

        foreach ($inputs as $key => $value):
            $id = $value['Id'];
            $object = PMSSubmissionDetail::find($id);
            $object->fill($value);
            $object->update();
        endforeach;
        if ($type == 1) {
            $status = CONST_PMSSTATUS_VERIFIED;
            $statusText = "Appraised";
        } else {
            $status = CONST_PMSSTATUS_APPROVED;
            $statusText = "Appraised";
        }

        $statusDetails = DB::select("select T1.Id, T1.SubmissionTime, T1.EmployeeId, T2.Email, T2.MobileNo, T2.EmpId,T2.Name as EmployeeName, concat(T2.Name,' of ', T3.Name) as Employee, Z1.Name as GradeStep, T4.Name as Position from pms_submission T1 join (mas_employee T2 join mas_gradestep Z1 on Z1.Id = T2.GradeStepId join mas_department T3 on T3.Id = T2.DepartmentId left join mas_position T4 on T4.Id = T2.PositionId) on T2.Id = T1.EmployeeId where T1.Id = (select A.SubmissionId from pms_submissiondetail A where A.Id = ?)", [$id]);
        $pmsSubmissionId = $statusDetails[0]->Id;
        $empId = $statusDetails[0]->EmpId;
        $currentPMSSubmissionDate = $statusDetails[0]->SubmissionTime;
        if ($file3) {
            $fileName = 'PMS File_' . $empId . '_2_' . randomString() . randomString() . '.' . $file3->getClientOriginalExtension();
            $file3->move($directory, $fileName);
            $filePath = $directory . '/' . $fileName;
            DB::table('pms_submission')->where('Id', $pmsSubmissionId)->update(['File3Path' => $filePath]);
        }
        if ($file4) {
            $fileName = 'PMS File_' . $empId . '_3_' . randomString() . randomString() . '.' . $file4->getClientOriginalExtension();
            $file4->move($directory, $fileName);
            $filePath = $directory . '/' . $fileName;
            DB::table('pms_submission')->where('Id', $pmsSubmissionId)->update(['File4Path' => $filePath]);
        }

        $employee = $statusDetails[0]->EmployeeName;
        $employeeId = $statusDetails[0]->EmployeeId;


        $this->saveStatus($pmsSubmissionId, $status, Auth::id(), $remarks);

        //SEND SMS AND EMAIL TO NEXT LEVEL WITH LINK
        $actionByNameQuery = DB::table('mas_employee as T1')->join('mas_designation as T2', 'T2.Id', '=', 'T1.DesignationId')->where('T1.Id', Auth::id())->get(['T1.Name', 'T1.Gender', 'T1.Email', 'T1.MobileNo', 'T2.Name as Designation']);
        $actionByName = isset($actionByNameQuery[0]) ? ($actionByNameQuery[0]->Name . " (" . $actionByNameQuery[0]->Designation . ")") : "";
        $actionByName = $actionByName ? ($actionByNameQuery[0]->Gender == 'M' ? 'Mr. ' : 'Ms. ') . $actionByName : '';
        $redirectLink = url('/') . "?redirect=trackpms";
        $smsMessage = "Your PMS application has been $statusText by $actionByName. Please check your email for details.";
        $emailMessage = "Your PMS application has been $statusText by $actionByName.<br/><a href='$redirectLink'>Click here to track.</a>";

        $employeeEmail = $statusDetails[0]->Email;
        $employeeMobileNo = $statusDetails[0]->MobileNo;

        if ($employeeEmail && $employeeEmail != '@tashicell.com') {
            $this->sendMail($employeeEmail, $emailMessage, "Your PMS Evaluation has been $statusText by $actionByName.");
        }
        if ($employeeMobileNo) {
            $this->sendSMS($employeeMobileNo, $smsMessage);
        }
        if ($type == 1) {
            $empDetails = DB::table('mas_employee as T1')->join('mas_designation as A', 'A.Id', '=', 'T1.DesignationId')->join('mas_gradestep as T2', 'T2.Id', '=', 'T1.GradeStepId')->leftJoin('mas_section as T3', 'T3.Id', '=', 'T1.SectionId')->join('mas_department as T4', 'T4.Id', '=', 'T1.DepartmentId')->where('T1.Id', $employeeId)->get(array("T1.BasicPay", "T1.Gender as EmployeeGender", "T1.Name as EmployeeName", "T1.CIDNo", "T2.Name as GradeStep", "T3.Name as Section", "T4.Name as Department", "T2.PayScale", "T1.GradeStepId", 'A.Name as Designation'));
            if (count($empDetails) == 0) {
                abort(404);
            }

            $secondLevelEmployeeIdQuery = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.ReportingLevel2EmployeeId')->whereNotNull("T1.ReportingLevel2EmployeeId")->where('T1.EmployeeId', $employeeId)->get(['T2.Email', 'T2.MobileNo']);

            if (count($secondLevelEmployeeIdQuery) > 0) {
                foreach ($secondLevelEmployeeIdQuery as $secondLevelEmployee):
                    if ($secondLevelEmployee->Email && $secondLevelEmployee->Email != '@tashicell.com') {
                        $secondLevelEmailArray[] = $secondLevelEmployee->Email;
                    }
                    if ($secondLevelEmployee->MobileNo) {
                        $secondLevelMobileNoArray[] = $secondLevelEmployee->MobileNo;
                    }
                endforeach;
            }

            $employeeGender = $empDetails[0]->EmployeeGender;
            $employeeName = $empDetails[0]->EmployeeName;
            $employeeName = (($employeeGender == 'M') ? 'Mr. ' : 'Ms. ') . $employeeName;
            $employeeCIDNo = $empDetails[0]->CIDNo;
            $designation = $empDetails[0]->Designation;
            $department = $empDetails[0]->Department;
            $section = $empDetails[0]->Section;

            $redirectLink = url('/') . "?redirect=processpms/" . $pmsSubmissionId;
            $smsMessage = "PMS of " . $employeeName . " (" . $employeeCIDNo . ") has been appraised and is at your desk. Please check your email for details.";
            $emailMessage = "PMS of " . $employeeName . " ($designation) of $section, $department has been appraised and is at your desk. <br/><a href='$redirectLink'>Click here to evaluate.</a>";

            if (count($secondLevelEmailArray) > 0) {
                foreach ($secondLevelEmailArray as $secondLevelEmail):
                    $this->sendMail($secondLevelEmail, $emailMessage, "PMS of " . $employeeName . " has been appraised and is at your desk.");
                endforeach;
            }
            if (count($secondLevelMobileNoArray) > 0) {
                foreach ($secondLevelMobileNoArray as $secondLevelMobileNo):
                    $this->sendSMS($secondLevelMobileNo, $smsMessage);
                endforeach;
            }
        }

        if ($type != 1) {
            $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$currentPMSSubmissionDate]);
            $pmsId = $pmsNumber[0]->Id;

            $finalScore = $this->getFinalScore($pmsSubmissionId);

            DB::delete("DELETE FROM pms_historical where PMSNumberId = ? and EmpId = ?", [$pmsId, $empId]);
            DB::table('pms_submission')->where('Id', $pmsSubmissionId)->update(['PMSOutcomeId' => CONST_PMSOUTCOME_NOACTION]);
            DB::insert("INSERT INTO pms_historical (CIDNo,EmpId,PMSNumberId,PMSSubmissionId,PMSSCore,PMSResult,PMSRemarks) SELECT T2.CIDNo,T2.EmpId, ?,?, ?, D.Name, T1.FinalRemarks from pms_submission T1 join mas_employee T2 on T2.Id = T1.EmployeeId left join mas_pmsoutcome D on D.Id = T1.PMSOutcomeId where T1.Id = ?", [$pmsId, $pmsSubmissionId, $finalScore, $pmsSubmissionId]);
        }
        //END SEND SMS
        return redirect('appraisepms')->with('successmessage', 'You have appraised PMS for ' . $employee . ' (Emp Id: ' . $empId . ')');
    }

    public function downloadFile(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $file = $request->input('file');
        return response()->download($file);
    }

    public function sendBack($id, Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $firstLevelEmailArray = [];
        $firstLevelMobileNoArray = [];
        $remarks = $request->input('remarks');
        $currentApplicationStatusQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $id)->pluck('LastStatusId');

        if (!count($currentApplicationStatusQuery)) {
            abort(404);
        }
        $currentApplicationStatusId = $currentApplicationStatusQuery[0];

        $employeeIdQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $id)->pluck('EmployeeId');
        $employeeId = $employeeIdQuery[0];

        if ($currentApplicationStatusId == CONST_PMSSTATUS_DRAFT) {
            $currentApplicationStatusQuery = DB::table('pms_submissionhistory')->where('SubmissionId', $id)->orderBy('StatusUpdateTime', 'DESC')->skip(1)->take(1)->pluck('PMSStatusId');
            $currentApplicationStatusId = $currentApplicationStatusQuery[0];
        }

        if ($currentApplicationStatusId == CONST_PMSSTATUS_VERIFIED) {
            $newStatusId = CONST_PMSSTATUS_SENTBACKBYAPPROVER;
        } else if ($currentApplicationStatusId == CONST_PMSSTATUS_SUBMITTED) {
            $details = DB::select("select T1.ReportingLevel2EmployeeId from mas_hierarchy T1 where T1.EmployeeId = ? and T1.ReportingLevel2EmployeeId is not null", [$employeeId]);
            $reportingLevel2EmployeeId = $details[0]->ReportingLevel2EmployeeId ?? false;
            if (!$reportingLevel2EmployeeId) {
                $newStatusId = CONST_PMSSTATUS_SENTBACKBYAPPROVER;
            } else {
                $newStatusId = CONST_PMSSTATUS_SENTBACKBYVERIFIER;
            }
        } else if ($currentApplicationStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER) {
            $newStatusId = CONST_PMSSTATUS_SENTBACKBYVERIFIER;
        } else {
            abort(404);
        }
        $this->saveStatus($id, $newStatusId, Auth::id(), $remarks);
        DB::update("UPDATE pms_submissionmultiple T1 SET T1.Status = 2 where T1.SubmissionId = ?", [$id]);
        $statusDetails = DB::select("select T1.Id, concat(T2.Name,' of ', T3.Name) as Employee, A.Name as GradeStep from pms_submission T1 join (mas_employee T2 join mas_gradestep A on A.Id = T2.GradeStepId join mas_department T3 on T3.Id = T2.DepartmentId left join mas_position T4 on T4.Id = T2.PositionId) on T2.Id = T1.EmployeeId where T1.Id = ?", [$id]);
        $employee = $statusDetails[0]->Employee;
        $gradeStep = $statusDetails[0]->GradeStep;

        //SEND SMS AND EMAIL TO APPRAISEE WITH LINK
        $actionByNameQuery = DB::table('mas_employee as T1')->join('mas_designation as T2', 'T2.Id', '=', 'T1.DesignationId')->where('T1.Id', Auth::id())->get(['T1.Name', 'T2.Name as Designation']);
        $actionByName = isset($actionByNameQuery[0]) ? ($actionByNameQuery[0]->Name . " (" . $actionByNameQuery[0]->Designation . " )") : "";
        if ($currentApplicationStatusId == CONST_PMSSTATUS_SUBMITTED || $currentApplicationStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER) {
            $statusText = "sent back";
            $redirectLink = url('/') . "?redirect=resubmit/$id";
            $smsMessage = "Your PMS application has been $statusText by $actionByName. Please check your email for details.";
            $emailMessage = "Your PMS application has been $statusText by $actionByName.<br/><em>$remarks</em><br/><a href='$redirectLink'>Click here to review and resubmit.</a>";


            $employeeDetails = DB::table('mas_employee')->where('Id', $employeeId)->get(['Email', 'MobileNo']);
            $employeeEmail = $employeeDetails[0]->Email;
            $employeeMobileNo = $employeeDetails[0]->MobileNo;

            if ($employeeEmail && $employeeEmail != '@tashicell.com') {
                $this->sendMail($employeeEmail, $emailMessage, "Your PMS Evaluation has been $statusText by $actionByName.");
            }
            if ($employeeMobileNo) {
                $this->sendSMS($employeeMobileNo, $smsMessage);
            }
        } else {
            $empDetails = DB::table('mas_employee as T1')->join('mas_designation as A', 'A.Id', '=', 'T1.DesignationId')->join('mas_gradestep as T2', 'T2.Id', '=', 'T1.GradeStepId')->join('mas_section as T3', 'T3.Id', '=', 'T1.SectionId')->join('mas_department as T4', 'T4.Id', '=', 'T1.DepartmentId')->where('T1.Id', $employeeId)->get(array("T1.BasicPay", "T1.Name as EmployeeName", "T1.CIDNo", "T2.Name as GradeStep", "T3.Name as Section", "T4.Name as Department", "T2.PayScale", "T1.GradeStepId", "A.Name as Designation"));
            if (count($empDetails) == 0) {
                abort(404);
            }

            $firstLevelEmployeeIdQuery = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.ReportingLevel1EmployeeId')->where('T1.EmployeeId', $employeeId)->get(['T2.Email', 'T2.MobileNo']);

            foreach ($firstLevelEmployeeIdQuery as $firstLevelEmployee):
                if ($firstLevelEmployee->Email && $firstLevelEmployee->Email != '@tashicell.com') {
                    $firstLevelEmailArray[] = $firstLevelEmployee->Email;
                }
                if ($firstLevelEmployee->MobileNo) {
                    $firstLevelMobileNoArray[] = $firstLevelEmployee->MobileNo;
                }

            endforeach;

            $employeeName = $empDetails[0]->EmployeeName;
            $employeeCIDNo = $empDetails[0]->CIDNo;
            $designation = $empDetails[0]->Designation;
            $department = $empDetails[0]->Department;
            $section = $empDetails[0]->Section;

            $redirectLink = url('/') . "?redirect=processpms/" . $id;
            $smsMessage = "PMS of " . $employeeName . " (" . $employeeCIDNo . ") has been sent back by $actionByName and is at your desk. Please check your email for details.";
            $emailMessage = "PMS of " . $employeeName . " ($designation) of $section, $department has been sent back and is at your desk. <br/><a href='$redirectLink'>Click here to evaluate.</a>";

            foreach ($firstLevelEmailArray as $firstLevelEmail):
                $this->sendMail($firstLevelEmail, $emailMessage, "PMS of " . $employeeName . " has been sent back by $actionByName and is at your desk.");
            endforeach;
            foreach ($firstLevelMobileNoArray as $firstLevelMobileNo):
                $this->sendSMS($firstLevelMobileNo, $smsMessage);
            endforeach;
        }
        //END SEND

        return redirect('appraisepms')->with('successmessage', 'PMS evaluation of ' . $employee . ' (' . $gradeStep . ') has been sent back');
    }

    public function trackPMS(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['Status', 'PMSNumber', 'StartDate']);
        $status = $statusQuery[0]->Status;
        $currentPMSNumber = $statusQuery[0]->PMSNumber;
        $currentPMSStartDate = $statusQuery[0]->StartDate;

        if ($status == 2 || $status == 3) {
            $previousPMS = (int) $currentPMSNumber - 1;
            $previousPMSDateQuery = DB::table('sys_pmsnumber')->where('PMSNumber', $previousPMS)->pluck('StartDate');
            $previousPMSDate = $previousPMSDateQuery[0];

            $endingOfCurrentPMS = date_sub(date_create($currentPMSStartDate), date_interval_create_from_date_string("1 Days"));
            $endingDateOfCurrentPMS = $endingOfCurrentPMS->format('Y-m-d');

            $message = "PMS for " . convertDateToClientFormat($previousPMSDate) . " to " . convertDateToClientFormat($endingDateOfCurrentPMS) . " has been closed by HR Admin.";

            return view('application.pmslist')->with('status', $status)->with('closedMessage', $message);
        }

        $today = strtotime(date('Y-m-d'));
        $withinFirstPMSOfYear = false;
        $withinSecondPMSOfYear = false;
        $notWithinPMSPeriod = false;
        $alreadySubmitted = false;
        $hasLevel2 = false;
        $pmsDetails = [];
        $pmsHistory = [];

        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-09-31'))) {
            $withinSecondPMSOfYear = true;
            $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-07-01'), date('Y-09-31')])->count();
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-05-31'))) {
                $withinFirstPMSOfYear = true;
                $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-01-01'), date('Y-05-31')])->count();
            }
        }

        if ($withinFirstPMSOfYear || $withinSecondPMSOfYear) {
            if ($pmsCount > 0) {
                $alreadySubmitted = true;
                $details = DB::select("select T1.ReportingLevel2EmployeeId from mas_hierarchy T1 where T1.EmployeeId = ? and T1.ReportingLevel2EmployeeId is not null", [Auth::id()]);
                $reportingLevel2EmployeeId = isset($details[0]->ReportingLevel2EmployeeId) ? $details[0]->ReportingLevel2EmployeeId : false;
                if ($reportingLevel2EmployeeId) {
                    $hasLevel2 = true;
                }
                if ($withinFirstPMSOfYear) {
                    $pmsDetails = DB::select("select T1.Id,T1.SubmissionTime, T1.OfficeOrderPath, T2.Name as PMSOutcome, DATE_FORMAT(T1.OutcomeDateTime,'%D %M, %Y at %l:%i %p') as OutcomeDateTime, T1.LastStatusId, T1.StatusByEmployeeId, T1.PMSOutcomeId, T1.OfficeOrderEmailed,T1.FilePath, (select GROUP_CONCAT(CONCAT('<tr><td><strong><em>',DATE_FORMAT(A.StatusUpdateTime,'%D %M, %Y %l:%i %p'),':</strong></em></td><td><strong><em>',B.Name,'</strong></em></td>', '<td><strong><em>',C.Name,'</strong></em>', case when A.Remarks is not null and A.Remarks <> '' then concat('<br/><em>',A.Remarks,'</em>') else '' end,'</td></tr>') order by A.StatusUpdateTime SEPARATOR '<br/><br/>') from pms_submissionhistory A join mas_pmsstatus B on A.PMSStatusId = B.Id join mas_employee C on C.Id = A.StatusByEmployeeId where A.SubmissionId = T1.Id) as History from viewpmssubmissionwithlaststatus T1 left join mas_pmsoutcome T2 on T2.Id = T1.PMSOutcomeId where T1.EmployeeId = ? and (DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [Auth::id(), date('Y-01-01'), date('Y-05-31')]);
                    $pmsHistory = DB::table("pms_submissionhistory as T1")
                        ->join('pms_submission as T2', 'T2.Id', '=', 'T1.SubmissionId')
                        ->join('mas_employee as T3', 'T3.Id', '=', 'T1.StatusByEmployeeId')
                        ->join('mas_pmsstatus as T4', 'T4.Id', '=', 'T1.PMSStatusId')
                        ->leftJoin('mas_designation as T5', 'T5.Id', '=', 'T3.DesignationId')
                        ->whereRaw("T2.EmployeeId = ? and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?)", [Auth::id(), date('Y-01-01'), date('Y-05-31')])
                        ->orderBy('T1.StatusUpdateTime')
                        ->get(array('T1.StatusUpdateTime', 'T1.PMSStatusId', DB::raw("(select GROUP_CONCAT(concat(A.Name, ' (',C.Name,')') SEPARATOR ', ') from mas_employee A join mas_designation C on C.Id = A.DesignationId join pms_submissionmultiple B on B.AppraisedByEmployeeId = A.Id and B.ForLevel = 1 where B.SubmissionId = T1.SubmissionId) as Level1MultipleNames"), DB::raw("(select GROUP_CONCAT(concat(A.Name,' (',C.Name,')') SEPARATOR ', ') from mas_employee A join mas_designation C on C.Id = A.DesignationId join pms_submissionmultiple B on B.AppraisedByEmployeeId = A.Id and B.ForLevel = 2 where B.SubmissionId = T1.SubmissionId) as Level2MultipleNames"), 'T5.Name as LastStatusDesignation', 'T3.Id as StatusUpdatedById', DB::raw('T3.Name as StatusUpdatedBy'), 'T1.Remarks', 'T4.Name as Status'));
                } else {
                    $pmsDetails = DB::select("select T1.Id,T1.SubmissionTime, T1.OfficeOrderPath, T2.Name as PMSOutcome, DATE_FORMAT(T1.OutcomeDateTime,'%D %M, %Y at %l:%i %p') as OutcomeDateTime, T1.LastStatusId, T1.StatusByEmployeeId, T1.PMSOutcomeId, T1.OfficeOrderEmailed,T1.FilePath, (select GROUP_CONCAT(CONCAT('<tr><td><strong><em>',DATE_FORMAT(A.StatusUpdateTime,'%D %M, %Y %l:%i %p'),':</strong></em></td><td><strong><em>',B.Name,'</strong></em></td>', '<td><strong><em>',C.Name,'</strong></em>', case when A.Remarks is not null and A.Remarks <> '' then concat('<br/><em>',A.Remarks,'</em>') else '' end,'</td></tr>') order by A.StatusUpdateTime SEPARATOR '<br/><br/>') from pms_submissionhistory A join mas_pmsstatus B on A.PMSStatusId = B.Id join mas_employee C on C.Id = A.StatusByEmployeeId where A.SubmissionId = T1.Id) as History from viewpmssubmissionwithlaststatus T1 left join mas_pmsoutcome T2 on T2.Id = T1.PMSOutcomeId where T1.EmployeeId = ? and (DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [Auth::id(), date('Y-07-01'), date('Y-09-31')]);
                    $pmsHistory = DB::table("pms_submissionhistory as T1")
                        ->join('pms_submission as T2', 'T2.Id', '=', 'T1.SubmissionId')
                        ->join('mas_employee as T3', 'T3.Id', '=', 'T1.StatusByEmployeeId')
                        ->join('mas_pmsstatus as T4', 'T4.Id', '=', 'T1.PMSStatusId')
                        ->leftJoin('mas_designation as T5', 'T5.Id', '=', 'T3.DesignationId')
                        ->whereRaw("T2.EmployeeId = ? and (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= ?)", [Auth::id(), date('Y-07-01'), date('Y-09-31')])
                        ->orderBy('T1.StatusUpdateTime')
                        ->get(array('T1.StatusUpdateTime', 'T1.PMSStatusId', DB::raw("(select GROUP_CONCAT(concat(A.Name, ' (',C.Name,')') SEPARATOR ', ') from mas_employee A join mas_designation C on C.Id = A.DesignationId join pms_submissionmultiple B on B.AppraisedByEmployeeId = A.Id and B.ForLevel = 1 where B.SubmissionId = T1.SubmissionId) as Level1MultipleNames"), DB::raw("(select GROUP_CONCAT(concat(A.Name,' (',C.Name,')') SEPARATOR ', ') from mas_employee A join mas_designation C on C.Id = A.DesignationId join pms_submissionmultiple B on B.AppraisedByEmployeeId = A.Id and B.ForLevel = 2 where B.SubmissionId = T1.SubmissionId) as Level2MultipleNames"), 'T5.Name as LastStatusDesignation', 'T3.Id as StatusUpdatedById', DB::raw('T3.Name as StatusUpdatedBy'), 'T1.Remarks', 'T4.Name as Status'));
                }
            }
        } else {
            $notWithinPMSPeriod = true;
        }

        return view('application.pmslist')->with('hasLevel2', $hasLevel2)->with('pmsHistory', $pmsHistory)->with('pmsDetails', $pmsDetails)->with('notWithinPMSPeriod', $notWithinPMSPeriod)->with('alreadySubmitted', $alreadySubmitted);
    }

    public function resubmit($id, $saved = false)
    {
        $scoresOfSubordinate = [];
        $noOfEmployees = 0;
        $subordinateScorePercentage = false;
        $lastStatusQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $id)->pluck('LastStatusId');
        if (count($lastStatusQuery) == 0) {
            abort(404);
        }
        $lastStatusId = $lastStatusQuery[0];

        $lastStatusEmployeeQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $id)->pluck('StatusByEmployeeId');
        $lastStatusEmployeeId = $lastStatusEmployeeQuery[0];

        $loggedInEmployeeId = Auth::id();

        $details = DB::select("select T1.ReportingLevel2EmployeeId from mas_hierarchy T1 where T1.EmployeeId = ? and T1.ReportingLevel2EmployeeId is not null", [$loggedInEmployeeId]);
        $reportingLevel2EmployeeId = isset($details[0]->ReportingLevel2EmployeeId) ? $details[0]->ReportingLevel2EmployeeId : false;

        if (($lastStatusId == CONST_PMSSTATUS_DRAFT && $lastStatusEmployeeId == Auth::id()) || ($lastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER) || ($lastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER && !$reportingLevel2EmployeeId)) {
            $filePathsQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $id)->get(['FilePath', 'File2Path', 'File3Path', 'File4Path']);
            $filePath = $filePathsQuery[0]->FilePath;
            $filePath2 = $filePathsQuery[0]->File2Path;
            $filePath3 = $filePathsQuery[0]->File3Path;
            $filePath4 = $filePathsQuery[0]->File4Path;
            $details = DB::table("pms_submissiondetail as T1")->where('T1.SubmissionId', $id)->orderBy('T1.DisplayOrder')->get(array('T1.Id', 'T1.AssessmentArea', 'T1.Weightage', 'T1.SelfRating', DB::raw("(select GROUP_CONCAT(CONCAT('<strong><em>',DATE_FORMAT(A.StatusUpdateTime,'%D %M, %Y %l:%i %p'),':</strong></em> Status changed to <strong><em>',B.Name,'</strong></em>', ' by <strong><em>',C.Name,'</strong></em>', case when A.Remarks is not null and A.Remarks <> '' then concat('<br/><em>',A.Remarks,'</em>') else '' end) order by A.StatusUpdateTime SEPARATOR '<br/><br/>') from pms_submissionhistory A join mas_pmsstatus B on A.PMSStatusId = B.Id join mas_employee C on C.Id = A.StatusByEmployeeId where A.SubmissionId = T1.SubmissionId) as History")));
            if (count($details) == 0) {
                //if(Auth::user()->EmpId == 541){dd('here');}
                abort(404);
            }
            $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
            $pmsId = $this->getCurrentRound();

            $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['Status', 'PMSNumber', 'StartDate']);
            $status = $statusQuery[0]->Status;
            $currentPMSNumber = $statusQuery[0]->PMSNumber;
            $currentPMSStartDate = $statusQuery[0]->StartDate;

            $userPositionId = Auth::user()->PositionId;
            $userDepartmentId = Auth::user()->DepartmentId;

            $userPositionDepartmentIdQuery = DB::table('mas_positiondepartment')->where('DepartmentId', $userDepartmentId)->where('PositionId', $userPositionId)->pluck('Id');
            $userPositionDepartmentId = isset($userPositionDepartmentIdQuery[0]) ? $userPositionDepartmentIdQuery[0] : false;

            $assessmentAreas = DB::select("select T2.Description,T2.Weightage,T2.ApplicableToLevel2, 0 as DisplayOrder from mas_positiondepartmentrating T1 join mas_positiondepartmentratingcriteria T2 on T2.PositionDepartmentRatingId = T1.Id where T1.PositionDepartmentId = ? and T2.Weightage = (select max(B.Weightage) from mas_positiondepartmentrating A join mas_positiondepartmentratingcriteria B on B.PositionDepartmentRatingId = A.Id where A.PositionDepartmentId = ?) union all select T2.Description,T2.Weightage,T2.ApplicableToLevel2, T2.DisplayOrder from mas_positiondepartmentrating T1 join mas_positiondepartmentratingcriteria T2 on T2.PositionDepartmentRatingId = T1.Id where T1.PositionDepartmentId = ? and T2.Weightage <> (select max(B.Weightage) from mas_positiondepartmentrating A join mas_positiondepartmentratingcriteria B on B.PositionDepartmentRatingId = A.Id where A.PositionDepartmentId = ?) order by DisplayOrder", [$userPositionDepartmentId, $userPositionDepartmentId, $userPositionDepartmentId, $userPositionDepartmentId]);

            if ($userDepartmentId == 7) {
                $goalOutOf = $assessmentAreas[0]->Weightage;
                $noOfEmployeesQuery = DB::select("SELECT count(distinct T1.Id) as NoOfEmployees FROM `pms_submission` T1 join mas_employee T2 on T2.Id = T1.EmployeeId WHERE T1.SubmissionTime >= '2020-01-01' and T2.DepartmentId = ?", [$userDepartmentId]);
                $noOfEmployees = $noOfEmployeesQuery[0]->NoOfEmployees;
                if ($userPositionId == CONST_POSITION_HOD) {
                    $scoresOfSubordinate = DB::select("select T1.Name as Section, coalesce((select ROUND(AVG(coalesce(coalesce(A.Level2Rating,A.Level1Rating),A.SelfRating)/A.Weightage * ?),2) from pms_submissiondetail A join pms_submission B on B.Id = A.SubmissionId where B.SectionId = T1.Id and B.EmployeeId <> ? and A.DisplayOrder = 0 and B.SubmissionTime >= ?),0) as Score, (select count(A.Id) from pms_submission A where A.SectionId = T1.Id and A.SubmissionTime >= ?) as SectionEmployeeCount from mas_section T1 where T1.DepartmentId = ?", [$goalOutOf, Auth::id(), $currentPMSStartDate, $currentPMSStartDate, $userDepartmentId]);
                }
                if ($userPositionId == CONST_POSITION_HOS) {
                    $subordinateScorePercentage = DB::table('pms_employeegoal')->where('EmployeeId', Auth::id())->orderBy('created_at', 'DESC')->take(1)->value('SubordinateScorePercentage');
                    $scoresOfSubordinate = DB::select("select T1.Name as Section, coalesce((select ROUND(AVG(coalesce(A.Level1Rating,A.SelfRating)/A.Weightage * ?),2) from pms_submissiondetail A join pms_submission B on B.Id = A.SubmissionId where B.SectionId = T1.Id and A.DisplayOrder = 0 and B.EmployeeId <> ? and B.SubmissionTime >= ?),0) as Score, (select count(A.Id) from pms_submission A where A.SectionId = T1.Id and A.SubmissionTime >= ?) as SectionEmployeeCount from mas_section T1 where T1.Id = ?", [$goalOutOf, Auth::id(), $currentPMSStartDate, $currentPMSStartDate, Auth::user()->SectionId]);
                }
            }

            $goalAchievementScore = false;
            if (true) {
                $goalAchievementScore = DB::table("pms_employeegoal as T1")
                    ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
                    ->where('T1.SysPmsNumberId', $pmsId)
                    ->where('T1.EmployeeId', Auth::id())
                    ->sum("T2.SelfScore");

                if ($goalAchievementScore && count($assessmentAreas) > 0) {
                    $goalAchievementScore = ($goalAchievementScore / 100) * $assessmentAreas[0]->Weightage;
                }
            }
            return view('application.pmssubmit')->with('subordinateScorePercentage', $subordinateScorePercentage)->with('goalAchievementScore', $goalAchievementScore)->with('noOfEmployees', $noOfEmployees)->with('scoresOfSubordinate', $scoresOfSubordinate)->with('saved', $saved)->with('filePath', $filePath)->with('filePath3', $filePath3)->with('filePath4', $filePath4)->with('filePath2', $filePath2)->with('id', $id)->with('details', $details);
        } else {
            //if(Auth::user()->EmpId == 541){dd(($lastStatusId  == CONST_PMSSTATUS_SENTBACKBYAPPROVER)?1:0);}
            abort(404);
        }
    }

    public function postResubmit(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $firstLevelEmailArray = [];
        $firstLevelMobileNoArray = [];
        $file = $request->file('File');
        $file2 = $request->file('File2');
        $id = $request->Id;
        $inputs = $request->input('pmssubmissiondetail');
        $directory = 'uploads/' . date('Y') . '/' . date('m');

        if ($file) {
            $extension = $file->getClientOriginalExtension();
            if (!$this->in_arrayi($extension, ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
                return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files or excel or word documents');
            }

            $fileName = 'PMS File_' . Auth::user()->EmpId . '_' . randomString() . randomString() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $updateArray['FilePath'] = $directory . '/' . $fileName;
        }
        if ($file2) {
            $extension2 = $file2->getClientOriginalExtension();
            if (!$this->in_arrayi($extension2, ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
                return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files or excel or word documents');
            }
            $fileName2 = 'PMS Additional Document_' . Auth::user()->EmpId . '_' . randomString() . randomString() . '.' . $file2->getClientOriginalExtension();
            $file2->move($directory, $fileName2);
            $updateArray['File2Path'] = $directory . '/' . $fileName2;
        }
        if ($file || $file2) {
            $updateArray['updated_at'] = date('Y-m-d H:i:s');
            $updateArray['EditedBy'] = Auth::id();
            $updateObject = PMSSubmission::find($id);
            $updateObject->fill($updateArray);
            $updateObject->update();
        }
        foreach ($inputs as $key => $value):
            $detailObject = PMSSubmissionDetail::find($value['Id']);
            $value['updated_at'] = date('Y-m-d H:i:s');
            $value['EditedBy'] = Auth::id();
            $detailObject->fill($value);
            $detailObject->update();
        endforeach;

        $firstLevelEmployeeIdQuery = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.ReportingLevel1EmployeeId')->where('T1.EmployeeId', Auth::id())->get(['T2.Email', 'T2.MobileNo']);

        if (count($firstLevelEmployeeIdQuery) > 0) {
            foreach ($firstLevelEmployeeIdQuery as $firstLevelEmployeeId):
                if ($firstLevelEmployeeId->Email && $firstLevelEmployeeId->Email != '@tashicell.com') {
                    $firstLevelEmailArray[] = $firstLevelEmployeeId->Email;
                }
                if ($firstLevelEmployeeId->MobileNo) {
                    $firstLevelMobileNoArray[] = $firstLevelEmployeeId->MobileNo;
                }
            endforeach;
        }

        //SEND SMS AND EMAIL TO NEXT LEVEL WITH LINK
        $empDetails = DB::table('mas_employee as T1')->join('mas_designation as A', 'A.Id', '=', 'T1.DesignationId')->join('mas_gradestep as T2', 'T2.Id', '=', 'T1.GradeStepId')->leftJoin('mas_section as T3', 'T3.Id', '=', 'T1.SectionId')->join('mas_department as T4', 'T4.Id', '=', 'T1.DepartmentId')->where('T1.Id', Auth::id())->get(array("T1.BasicPay", "T2.Name as GradeStep", "T3.Name as Section", "T4.Name as Department", "T2.PayScale", "T1.GradeStepId", "A.Name as Designation"));

        $designation = $empDetails[0]->Designation;
        $department = $empDetails[0]->Department;
        $section = $empDetails[0]->Section;

        $redirectLink = url('/') . "?redirect=processpms/" . $id;
        $salutation = Auth::user()->Gender == 'M' ? "Mr. " : "Ms. ";
        $smsMessage = $salutation . Auth::user()->Name . " (" . $designation . ") has submitted PMS. Please check your email for details.";
        $emailMessage = $salutation . Auth::user()->Name . " ($designation) of $section, $department has submitted PMS. <br/><a href='$redirectLink'>Click here to evaluate.</a>";

        if (count($firstLevelEmailArray) > 0) {
            foreach ($firstLevelEmailArray as $firstLevelEmail):
                $this->sendMail($firstLevelEmail, $emailMessage, $salutation . Auth::user()->Name . " has submitted PMS");
            endforeach;
        }
        if (count($firstLevelMobileNoArray) > 0) {
            foreach ($firstLevelMobileNoArray as $firstLevelMobileNo):
                $this->sendSMS($firstLevelMobileNo, $smsMessage);
            endforeach;
        }
        //END SEND SMS

        $this->saveStatus($id, CONST_PMSSTATUS_SUBMITTED, Auth::id());
        return redirect('viewprofile')->with('successmessage', 'Your PMS has been submitted!');
    }

    public function getFinalize($id): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $settingForPayScale = DB::table("sys_settings")->where('Id', CONST_SYSSETTING_PAYSCALETYPE)->value("Value");
        $application = DB::select("select T1.Id,T1.NewPayScale,T1.FilePath,T1.LastStatusId,T2.ReportingLevel1EmployeeId, T2.ReportingLevel2EmployeeId, T1.OfficeOrderEmailed,T1.File2Path,T1.File3Path,T1.File4Path,T1.NewDesignationId,T1.NewGradeId,T1.NewLocation,T1.NewBasicPay,T1.NewGradeStepId,T1.NewSupervisorId,coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) as PMSOutcomeId, T5.HasBasicPayChange, T5.HasDesignationAndLocationChange, T5.HasPayChange, T5.HasPositionChange, T1.FinalRemarks, T1.OutcomeDateTime,T1.EmployeeId,T1.WeightageForLevel1, T1.Level2CriteriaType,T1.WeightageForLevel2, T3.Name as Level1Employee, T4.Name as Level2Employee from viewpmssubmissionwithlaststatus T1 join (mas_hierarchy T2 join (mas_employee T3 join mas_position A on A.Id = T3.PositionId) on T2.ReportingLevel1EmployeeId = T3.Id left join (mas_employee T4 join mas_position B on B.Id = T4.PositionId) on T4.Id = T2.ReportingLevel2EmployeeId) on T2.EmployeeId = T1.EmployeeId left join mas_pmsoutcome T5 on T5.Id = coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) where T1.Id = ? and T1.LastStatusId = ?", [$id, CONST_PMSSTATUS_APPROVED]);
        if (count($application) == 0) {
            abort(404);
        }
        $details = DB::select("select T1.CIDNo,T1.EmpId,T1.MobileNo,T1.Email,Z1.Name as GradeStep, Z1.PayScale,Z1.StartingSalary,Z1.EndingSalary,Z1.Increment,T1.BasicPay,T1.ProfilePicPath,T1.Extension,T1.Name,O.Name as DesignationLocation, T2.Name as Department, T4.Name as Section, T3.Name as Position from mas_employee T1 join mas_gradestep Z1 on Z1.Id = T1.GradeStepId join mas_designation O on O.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left join mas_position T3 on T3.Id = T1.PositionId left join mas_section T4 on T4.Id = T1.SectionId where T1.Id = ?", [$application[0]->EmployeeId]);

        $applicationDetails = DB::select("select T2.AssessmentArea, T2.ApplicableToLevel2,T2.Weightage, T2.SelfRating, T2.Level1Rating, T2.Level2Rating from viewpmssubmissionwithlaststatus T1 join pms_submissiondetail T2 on T2.SubmissionId = T1.Id where T1.Id = ?", [$id]);
        $pmsFile = DB::table('pms_submission')->where('Id', $id)->pluck('FilePath');
        $outcomes = DB::select("select * from mas_pmsoutcome order by Id");
        $gradesteps = DB::table('mas_gradestep as T1')
            ->whereRaw("coalesce(T1.Status,0)=1")
            ->select('T1.Id', 'T1.Name as GradeStep', 'T1.PayScale', 'T1.StartingSalary', 'T1.Increment', 'T1.EndingSalary')
            ->orderBy(DB::raw('T1.StartingSalary'))
            ->get();

        $empId = $details[0]->EmpId;
        $payRaiseSlabs = DB::select("SELECT * FROM mas_pay_scale_slab ORDER BY Raise");
        // value="{{$calculateNewPayScale[0]->NewBasicPay}}
        // $calculateNewPayScale = DB::select("SELECT CASE WHEN emp.BasicPay >= gst.StartingSalary && emp.BasicPay <= gst.EndingSalary THEN gst.Increment * 5 + emp.BasicPay ELSE 'Promote Now' END AS NewBasicPay FROM mas_employee emp JOIN mas_gradestep gst ON gst.Id = emp.GradeStepId WHERE emp.EmpId = ? ", [$empId]);

        $positions = DB::select("select Id, Name from mas_position order by Name");
        $designations = DB::table("mas_designation")->whereRaw("coalesce(Status,0)=1")->orderBy('Name')->get(array('Id', 'Name'));
        $disciplinaryDetails = DB::select("select RecordDate,Record, RecordDescription from rec_disciplinary where EmployeeId = ? order by RecordDate DESC", [$application[0]->EmployeeId]);
        $finalScore = DB::table('pms_submissionfinalscore')->where('SubmissionId', $id)->pluck('FinalScore');
        if (count($finalScore)):
            $finalScore = $finalScore[0];
        else:
            $finalScore = '';
        endif;

        $history = $this->getEmployeePMSHistory(trim($empId));
        $supervisors = DB::select("select Id, Name from mas_supervisor order by Name");
        $grades = DB::select("select Id, Name from mas_grade where coalesce(IsManagerialRole,0) = 1 and Id <> 9 order by Name");
        $pmsMultiple = DB::select("select T1.FilePath, T1.ForLevel, T1.AppraisedByEmployeeId, T2.Name as Appraiser from pms_submissionmultiple T1 join mas_employee T2 on T1.AppraisedByEmployeeId = T2.Id where T1.SubmissionId = ? and T1.FilePath is not null order by T1.created_at", [$id]);

        return view('application.pmsfinalize')->with('settingForPayScale', $settingForPayScale)->with('level1AppraiserCount', 0)->with('level2AppraiserCount', 0)->with('pmsMultiple', $pmsMultiple)->with('grades', $grades)->with('history', $history)->with('finalScore', $finalScore)->with('designations', $designations)->with('positions', $positions)->with('gradesteps', $gradesteps)->with('outcomes', $outcomes)->with('pmsFile', $pmsFile[0])->with('application', $application)->with('applicationDetails', $applicationDetails)->with('details', $details)->with('disciplinaryDetails', $disciplinaryDetails)->with('supervisors', $supervisors)->with('payRaiseSlabs', $payRaiseSlabs);
    }

    public function postFinalize(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $submit = $request->Submit;

        $id = $request->Id;

        $finalRemarks = $request->FinalRemarks;
        $paySlab = $request->PaySlab;
        $newPayScale = $request->NewPayScale;
        $newBasicPay = $request->NewBasicPay;
        $newGradeStepId = $request->NewGradeStepId;
        $outcomeId = $request->PMSOutcomeId;
        $newDesignationId = $request->NewDesignationId;
        $newLocation = $request->NewLocation;
        $finalScore = $request->FinalScore;

        $updateObject = PMSSubmission::find($id);
        $updateObject->updated_at = date('Y-m-d H:i:s');
        $updateObject->OutcomeDateTime = date('Y-m-d H:i:s');
        $updateObject->EditedBy = Auth::id();
        if ($submit == 1) {
            $updateObject->SavedPMSOutcomeId = $outcomeId;
        } else {
            $updateObject->PMSOutcomeId = $outcomeId;
        }

        $updateObject->NewPayScale = $newPayScale ?: NULL;
        $updateObject->NewBasicPay = $newBasicPay ?: NULL;
        $updateObject->NewGradeStepId = $newGradeStepId ?: NULL;
        $updateObject->PaySlab = $paySlab;

        $gradeId = $request->NewGradeId;
        $supervisorId = $request->NewSupervisorId;

        if (!$gradeId && $newGradeStepId) {
            $gradeIdQuery = DB::table('mas_gradestep')->where('Id', $newGradeStepId)->pluck('GradeId');
            $gradeId = isset($gradeIdQuery[0]) ? $gradeIdQuery[0] : NULL;
        }

        $positionIdQuery = DB::table('mas_position')->where('GradeStepId', $newGradeStepId)->where('SupervisorId', $supervisorId)->pluck('Id');
        $positionId = isset($positionIdQuery[0]) ? $positionIdQuery[0] : NULL;

        $updateObject->NewPositionId = $positionId;
        $updateObject->NewDesignationId = $newDesignationId ?: NULL;
        $updateObject->NewLocation = $newLocation ?: NULL;
        $updateObject->NewGradeId = $gradeId ?: NULL;
        $updateObject->NewSupervisorId = $supervisorId ?: NULL;

        $updateObject->FinalRemarks = $finalRemarks ?: NULL;
        $updateObject->update();

        $statusDetails = DB::select("select T1.Id, T2.EmpId, concat(T2.Name,' of ', T3.Name) as Employee, T4.Name as Position from pms_submission T1 join (mas_employee T2 join mas_department T3 on T3.Id = T2.DepartmentId left join mas_position T4 on T4.Id = T2.PositionId) on T2.Id = T1.EmployeeId where T1.Id = ?", [$id]);
        $employee = $statusDetails[0]->Employee;
        $empId = $statusDetails[0]->EmpId;

        $currentPMSSubmissionDateQuery = DB::table('pms_submission')->where('Id', $id)->pluck('SubmissionTime');
        $currentPMSSubmissionDateRaw = $currentPMSSubmissionDateQuery[0];
        $currentPMSSubmissionDate = date_format(date_create($currentPMSSubmissionDateRaw), 'Y-m-d');

        $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$currentPMSSubmissionDate]);
        $pmsId = $pmsNumber[0]->Id;
        DB::delete("DELETE FROM pms_historical where PMSNumberId = ? and EmpId = ?", [$pmsId, $empId]);
        DB::insert("INSERT INTO pms_historical (CIDNo,EmpId,PMSNumberId,PMSSubmissionId,PMSSCore,PMSResult,PMSRemarks) SELECT T2.CIDNo,T2.EmpId, ?,?, ?, D.Name, T1.FinalRemarks from pms_submission T1 join mas_employee T2 on T2.Id = T1.EmployeeId join mas_pmsoutcome D on D.Id = T1.PMSOutcomeId where T1.Id = ?", [$pmsId, $id, $finalScore, $id]);

        return redirect('appraisepms')->with('successmessage', "PMS for $employee has been processed"); //ADD EMPLOYEE ID, CID
    }

    public function viewPMSDetails($id, $type = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $level1MultipleScore = [];
        $level2MultipleScore = [];

        $application = DB::select("select T1.Id,T1.EmployeeId,T3.EmpId,T1.FinalRemarks,T1.PMSOutcomeId, T2.ReportingLevel1EmployeeId, T2.ReportingLevel2EmployeeId, T1.OfficeOrderEmailed, T1.LastStatusId, T1.LastRemarks, T6.Name as LastStatusEmployee, C.Name as Outcome, T1.OutcomeDateTime,T1.EmployeeId,T1.Level2CriteriaType,T1.WeightageForLevel1, T1.WeightageForLevel2, (select GROUP_CONCAT(A.Name SEPARATOR ', ') FROM mas_employee A join mas_hierarchy B on B.ReportingLevel1EmployeeId = A.Id where B.EmployeeId = T1.EmployeeId) as Level1Employee, (select GROUP_CONCAT(A.Name SEPARATOR ', ') FROM mas_employee A join mas_hierarchy B on B.ReportingLevel2EmployeeId = A.Id where B.EmployeeId = T1.EmployeeId) as Level2Employee from viewpmssubmissionwithlaststatus T1 left join mas_pmsoutcome C on C.Id = T1.PMSOutcomeId left join (mas_hierarchy T2 join (mas_employee T3 join mas_position A on A.Id = T3.PositionId) on T2.ReportingLevel1EmployeeId = T3.Id left join (mas_employee T4 join mas_position B on B.Id = T4.PositionId) on T4.Id = T2.ReportingLevel2EmployeeId) on T2.EmployeeId = T1.EmployeeId join mas_employee T6 on T6.Id = T1.StatusByEmployeeId where T1.Id = ?", [$id]);
        if (count($application) == 0) {
            abort(404);
        }

        $details = DB::select("select T1.Email,T1.Id,T1.Extension,T1.EmpId,T1.Name,O.Name as DesignationLocation, T2.Name as Department, T4.Name as Section, T3.Name as Position from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left join mas_position T3 on T3.Id = T1.PositionId left join mas_section T4 on T4.Id = T1.SectionId where T1.Id = ?", [$application[0]->EmployeeId]);
        $applicationDetails = DB::select("select T2.Id,T2.AssessmentArea, T2.Weightage, T2.SelfRating, T2.Level1Rating, T2.Level2Rating, T2.ApplicableToLevel2 from viewpmssubmissionwithlaststatus T1 join pms_submissiondetail T2 on T2.SubmissionId = T1.Id where T1.Id = ? order by T2.DisplayOrder", [$id]);
        $finalScore = DB::table('pms_submissionfinalscore')->where('SubmissionId', $id)->pluck('FinalScore');
        if (count($finalScore)):
            $finalScore = $finalScore[0];
        else:
            $finalScore = '';
        endif;

        $empId = $details[0]->EmpId;
        $pmsFile = DB::table('pms_submission')->where('Id', $id)->pluck('FilePath');
        $employeeId = $details[0]->Id;
        $level1Appraisers = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.ReportingLevel1EmployeeId')->where('T1.EmployeeId', $employeeId)->whereNotNull("T1.ReportingLevel1EmployeeId")->get(['T1.ReportingLevel1EmployeeId', 'T2.Name']);
        $level2Appraisers = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.ReportingLevel2EmployeeId')->where('T1.EmployeeId', $employeeId)->whereNotNull("T1.ReportingLevel2EmployeeId")->get(['T1.ReportingLevel2EmployeeId', 'T2.Name']);

        $level1AppraiserCount = count($level1Appraisers);
        $level2AppraiserCount = count($level2Appraisers);

        if ($level1AppraiserCount > 1) {
            foreach ($level1Appraisers as $level1Appraiser):
                $level1AppraiserId = $level1Appraiser->ReportingLevel1EmployeeId;
                $level1Multiple[$level1AppraiserId] = DB::table('pms_submissionmultiple as T1')
                    ->join('pms_submissionmultipledetail as T2', 'T2.SubmissionMultipleId', '=', 'T1.Id')
                    ->rightJoin('pms_submissiondetail as T3', 'T3.Id', '=', 'T2.SubmissionDetailId')
                    ->where('T1.SubmissionId', $id)
                    ->where('AppraisedByEmployeeId', $level1AppraiserId)
                    ->orderBy('T3.DisplayOrder')
                    ->get(['T2.SubmissionDetailId', 'T2.Score']);
                foreach ($level1Multiple[$level1AppraiserId] as $level1MultipleIndividual):
                    $level1MultipleScore[$level1AppraiserId][$level1MultipleIndividual->SubmissionDetailId] = $level1MultipleIndividual->Score;
                endforeach;
            endforeach;
        }
        if ($level2AppraiserCount > 1) {
            foreach ($level2Appraisers as $level2Appraiser):
                $level2AppraiserId = $level2Appraiser->ReportingLevel2EmployeeId;
                $level2Multiple[$level2AppraiserId] = DB::table('pms_submissionmultiple as T1')
                    ->join('pms_submissionmultipledetail as T2', 'T2.SubmissionMultipleId', '=', 'T1.Id')
                    ->rightJoin('pms_submissiondetail as T3', 'T3.Id', '=', 'T2.SubmissionDetailId')
                    ->where('T1.SubmissionId', $id)
                    ->where('AppraisedByEmployeeId', $level2AppraiserId)
                    ->orderBy('T3.DisplayOrder')
                    ->get(['T2.SubmissionDetailId', 'T2.Score']);
                foreach ($level2Multiple[$level2AppraiserId] as $level2MultipleIndividual):
                    $level2MultipleScore[$level2AppraiserId][$level2MultipleIndividual->SubmissionDetailId] = $level2MultipleIndividual->Score;
                endforeach;
            endforeach;
        }

        $profileDetails = DB::select("SELECT T1.EmpId,T1.Status,T3.GradeStepId,T1.CIDNo,T1.DateOfAppointment as DateOfRegularization, (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel1EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level1Name,
            (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel2EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level2Name, T4.Name as GradeStep, T4.PayScale,
            T1.Extension, T1.MobileNo, T1.DateOfBirth, T1.DateOfAppointment,T1.ProfilePicPath,T1.Extension,T1.Name,B.Name as DesignationLocation,
            T2.Name as Department,A.Name as Section, concat(Z1.Name,case when Z2.Id is null then '' else concat(' - Reporting to ',Z2.Name) end) as
            Position from mas_employee T1 join mas_designation B on B.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left
            join mas_section A on A.Id = T1.SectionId left join (mas_position T3 join mas_gradestep Z1 on Z1.Id = T3.GradeStepId left join mas_supervisor Z2
            on Z2.Id = T3.SupervisorId) on T3.Id = T1.PositionId left join mas_gradestep T4 on T4.Id = T1.GradeStepId /*left join (mas_hierarchy W1
            join mas_employee W2 on W2.Id = W1.ReportingLevel1EmployeeId left join mas_designation V1 on V1.Id = W2.DesignationId left join
            mas_employee W3 left join mas_designation V2 on V2.Id = W3.DesignationId on W3.Id = W1.ReportingLevel2EmployeeId) on W1.EmployeeId = T1.Id
            */where T1.Id = ?", [$application[0]->EmployeeId]);

        $history = DB::select("select GROUP_CONCAT(CONCAT('<strong><em>',DATE_FORMAT(A.StatusUpdateTime,'%D %M, %Y %l:%i %p'),':</strong></em> Status changed to <strong><em>',B.Name,'</strong></em>', ' by <strong><em>',C.Name,'</strong></em>', case when A.Remarks is not null and A.Remarks <> '' then concat('<br/><em>',A.Remarks,'</em>') else '' end) order by A.StatusUpdateTime SEPARATOR '<br/><br/>') as History from pms_submissionhistory A join mas_pmsstatus B on A.PMSStatusId = B.Id join mas_employee C on C.Id = A.StatusByEmployeeId where A.SubmissionId = ?", [$id]);

        $pmsHistory = $this->getEmployeePMSHistory(trim($empId));
        $submissionHistoryWithRemarks = $this->getPMSDetailsWithRemarks($id);

        $roleId = Auth::user()->RoleId;
        $isAdmin = false;
        if ((int)$roleId === 1) {
            $isAdmin = true;
        }

        return view('application.pmsdetails')->with('submissionHistoryWithRemarks', $submissionHistoryWithRemarks)->with('pmsHistory', $pmsHistory)->with('level1Appraisers', $level1Appraisers)->with('level2Appraisers', $level2Appraisers)->with('level1AppraiserCount', $level1AppraiserCount)->with('level2AppraiserCount', $level2AppraiserCount)->with('level1Multiple', $level1MultipleScore)->with('level2Multiple', $level2MultipleScore)->with('profileDetails', $profileDetails)->with('type', $type)->with('finalScore', $finalScore)->with('history', $history)->with('pmsFile', $pmsFile[0])->with('application', $application)->with('applicationDetails', $applicationDetails)->with('details', $details)->with('isAdmin', $isAdmin);
    }

    public function getPMSHistory(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $empId = Auth::user()->EmpId;
        $history = $this->getEmployeePMSHistory(trim($empId));
        return view('application.pmshistory')->with('history', $history);
    }

    public function getOfficeOrderIndex(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['Status', 'PMSNumber', 'StartDate']);
        $status = $statusQuery[0]->Status;
        $currentPMSNumber = $statusQuery[0]->PMSNumber;
        $currentPMSStartDate = $statusQuery[0]->StartDate;

        if ($status == 3) {
            $previousPMS = (int) $currentPMSNumber - 1;
            $previousPMSDateQuery = DB::table('sys_pmsnumber')->where('PMSNumber', $previousPMS)->pluck('StartDate');
            $previousPMSDate = $previousPMSDateQuery[0];

            $endingOfCurrentPMS = date_sub(date_create($currentPMSStartDate), date_interval_create_from_date_string("1 Days"));
            $endingDateOfCurrentPMS = $endingOfCurrentPMS->format('Y-m-d');

            $message = "PMS for " . convertDateToClientFormat($previousPMSDate) . " to " . convertDateToClientFormat($endingDateOfCurrentPMS) . " has been closed by HR Admin.";

            return view('application.pmsofficeorder')->with('status', $status)->with('closedMessage', $message);
        }

        $selectIndex = false;
        $employeeDeptId = Session::get('employeeDeptId');
        $today = strtotime(date('Y-m-d'));
        if ($today >= strtotime(date('Y-07-01'))) {
            $fromDate = date('Y-07-01');
            $toDate = date('Y-09-31');
        } else {
            $fromDate = date('Y-01-01');
            $toDate = date('Y-05-31');
        }

        $departments = $this->fetchActiveDepartments();
        $employees = [];
        $count = 0;
        $arrayCount = 0;
        foreach ($departments as $department):
            $employees[$department->Id] = DB::select("select T1.Id as SubmissionId, T2.Email, T2.Id as EmployeeId, T1.OfficeOrderPath,T1.OfficeOrderEmailed,T4.Name as Section, T2.Name, O.Name as DesignationLocation, T3.Name as Outcome from viewpmssubmissionwithlaststatus T1 join mas_employee T2 on T1.EmployeeId = T2.Id join mas_designation O on O.Id = T2.DesignationId join mas_pmsoutcome T3 on T3.Id = T1.PMSOutcomeId left join mas_section T4 on T4.Id = T1.SectionId where coalesce(T1.PMSOutcomeId,1) <> 1 and T1.DepartmentId = ? and (DATE_FORMAT(T1.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T1.SubmissionTime,'%Y-%m-%d') <= ?) order by T2.Name, T3.Name", [$department->Id, $fromDate, $toDate]);
            if (!count($employees[$department->Id])) {
                unset($employees[$department->Id]);
                unset($departments[$count]);
            } else {
                if ($department->Id == $employeeDeptId) {
                    $selectIndex = $arrayCount;
                }
                $arrayCount++;
            }
            $count++;
        endforeach;

        return view('application.pmsofficeorder')->with('selectIndex', $selectIndex)->with('departments', $departments)->with('employees', $employees);
    }

    public function getOfficeOrder($id): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $details = DB::select("select T1.Id,T2.Name as Employee, T1.SubmissionTime, T1.PMSOutcomeId,T1.NewPayScale,T1.NewBasicPay,T1.NewGradeStepId,T1.NewPositionId,T1.NewDesignationId from viewpmssubmissionwithlaststatus T1 join mas_employee T2 on T2.Id = T1.EmployeeId where T1.Id = ?", [$id]);
        $submissionTime = $details[0]->SubmissionTime;
        $submissionTime = date_create($submissionTime);
        $submissionDate = date_format($submissionTime, 'Y-m-d');

        $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$submissionDate]);
        $evaluationMeetingDate = $pmsNumber[0]->EvaluationMeetingDate;

        $hasWysiwyg = false;
        $hasEffectiveDate = false;

        $id = $details[0]->Id;
        $pmsOutcomeId = $details[0]->PMSOutcomeId;
        if (in_array((int) $pmsOutcomeId, [13, 12, 10, 8])) {
            $hasWysiwyg = true;
        } else {
            $hasEffectiveDate = true;
        }

        return view('application.getdetailsforofficeorder')->with('evaluationMeetingDate', $evaluationMeetingDate)->with('id', $id)->with('hasEffectiveDate', $hasEffectiveDate)->with('hasWysiwyg', $hasWysiwyg)->with('details', $details);
    }

    public function postGenerateOfficeOrder(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $id = $request->Id;
        $effectiveDate = $request->EffectiveDate;
        $evaluationMeetingDate = $request->EvaluationMeetingDate;
        $referenceNo = $request->ReferenceNo;
        $date = $request->Date;
        $content = $request->Content;
        $cc = '';
        $hodLabel = "General Manager";

        $details = DB::select("select T2.Name as Employee, T2.EmpId,T2.Gender,T2.CIDNo, T9.Name as Section, T6.name as GradeStep, T1.PaySlab, T1.BasicPay, T6.PayScale,coalesce(T3.Name,T2.Name) as DesignationLocation, T5.Name as NewDesignation, T4.Id as DepartmentId, T4.Name as Department, T3.Name as Designation, DATE_FORMAT(T1.SubmissionTime,'%Y-%m-%d') as SubmissionTime, T1.PMSOutcomeId,T7.PayScale as NewPayScale,T1.NewBasicPay,T7.Name as NewGradeStep,T1.NewGradeStepId,T1.NewPositionId,T1.NewDesignationId from viewpmssubmissionwithlaststatus T1 join mas_employee T2 on T2.Id = T1.EmployeeId join mas_designation O on O.Id = T2.DesignationId left join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T1.DepartmentId left join mas_designation T5 on T5.Id = T1.NewDesignationId left join mas_gradestep T6 on T6.Id = T1.GradeStepId left join mas_gradestep T7 on T7.Id = T1.NewGradeStepId left join mas_section T9 on T9.Id = T2.SectionId where T1.Id = ?", [$id]);

        $submissionTime = $details[0]->SubmissionTime;
        $pmsOutcomeId = (int) $details[0]->PMSOutcomeId;
        $submissionTime = date_create($submissionTime);
        $submissionDate = date_format($submissionTime, 'Y-m-d');

        $paySlab = $details[0]->PaySlab;

        $pmsNumber = DB::select("select T1.Id,T1.PMSNumber,T1.StartDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$submissionDate]);
        $pmsId = $pmsNumber[0]->Id;
        $currentPmsNumber = $pmsNumber[0]->PMSNumber;
        $startDate = $pmsNumber[0]->StartDate;

        $endingDateOfLastPMS = date_sub(date_create($startDate), date_interval_create_from_date_string("1 days"));
        $endingDateOfLastPMS = date_format($endingDateOfLastPMS, 'Y-m-d');
        $endingDateOfLastPMS = $this->formatDateNoOf($endingDateOfLastPMS);

        $lastPMSNumber = $currentPmsNumber - 1;
        $lastPMSNumber = ($lastPMSNumber == 0) ? 1 : $lastPMSNumber;
        $startingDateOfLastPMS = DB::table('sys_pmsnumber')->where('PMSNumber', $lastPMSNumber)->pluck('StartDate');
        $startingDateOfLastPMS = $startingDateOfLastPMS[0];
        $startingDateOfLastPMS = $this->formatDateNoOf($startingDateOfLastPMS);

        $pmsNumberLen = strlen($currentPmsNumber);
        $minusOne = $pmsNumberLen - 1;
        $lastDigit = substr($currentPmsNumber, $minusOne, 1);

        if ($lastDigit == 1) {
            $currentPmsNumber .= "<sup>st</sup>";
        } else if ($lastDigit == 2) {
            $currentPmsNumber .= "<sup>nd</sup>";
        } else if ($lastDigit == 3) {
            $currentPmsNumber .= "<sup>rd</sup>";
        } else {
            $currentPmsNumber .= "<sup>th</sup>";
        }

        $gender = $details[0]->Gender;
        $append = "";
        $pronoun = "";
        if ($gender == 'M') {
            $append = "Mr. ";
            $pronoun = "his";
        }
        if ($gender == 'F') {
            $append = "Ms. ";
            $pronoun = "her";
        }
        $employeeName = $append . $details[0]->Employee;

        //FETCH DEPT AND DESIGNATION FROM PMS TABLE
        $employeeDeptId = $details[0]->DepartmentId;
        $employeeDept = $details[0]->Department;
        $employeeDesignation = $details[0]->Designation;
        $employeeSection = $details[0]->Section;
        $gradeStep = $details[0]->GradeStep;
        $basicPay = round(str_replace(',', '', $details[0]->BasicPay), 0);
        $payScale = $details[0]->PayScale;
        $newGradeStep = $details[0]->NewGradeStep;
        $newBasicPay = str_replace(',', '', $details[0]->NewBasicPay);
        $newPayScale = $details[0]->NewPayScale;
        $employeeCID = $details[0]->CIDNo;
        $employeeEmpId = $details[0]->EmpId;
        //END

        if ($employeeDeptId == 1) {
            $hodLabel = "Executive Manager";
        }

        $newEmployeeDesignation = $details[0]->NewDesignation;

        DB::table('sys_pmsnumber')->where('Id', $pmsId)->update(['EvaluationMeetingDate' => $evaluationMeetingDate]);

        $evaluationMeetingDate = $evaluationMeetingDate ? $this->formatDate($evaluationMeetingDate) : NULL;
        $effectiveDate = $effectiveDate ? $this->formatDate($effectiveDate) : NULL;
        $date = $date ? $this->formatDateNoOf($date) : NULL;
        $notOrder = false;
        switch ($pmsOutcomeId):
            case 14:
                $content = "In pursuant to the decision taken by the Management Committee during the <strong><em>$currentPmsNumber Performance Evaluation Meeting</strong></em> held on $evaluationMeetingDate, <strong><em>$employeeName</strong></em>, holding Citizenship Identity Card number <strong><em>$employeeCID</strong></em>, $employeeDesignation under $employeeDept, Tashi InfoComm Limited, is hereby awarded Positional Promotion. <br/><br/><strong><em>$employeeName's</strong></em> change in position shall be implemented <strong><em>w.e.f. $effectiveDate</strong></em> and details are mentioned below:";
                $content .= "<br/><table style='margin-top:10px;'>
                                    <tr>
                                        <th style='padding:0 4px 2px 4px;'>Old Position</th>
                                        <th style='padding:0 4px 2px 4px;'>New Position</th>
                                    </tr>
                                    <tr>
                                        <td style='padding:0 4px 2px 4px;'>$employeeDesignation</td>
                                        <td style='padding:0 4px 2px 4px;'>$newEmployeeDesignation</td>
                                    </tr>
                            </table>";
                $cc = "1. $hodLabel, $employeeDept<br/>
                       2. $employeeName<br/>
                       3. Office copy";
                break;
            case 15:
                $content = "In pursuant to the decision taken by the Management Committee during the <strong><em>$currentPmsNumber Performance Evaluation Meeting</strong></em> held on $evaluationMeetingDate, <strong><em>$employeeName</strong></em>, holding Citizenship Identity Card number <strong><em>$employeeCID</strong></em>, $employeeDesignation under $employeeDept, Tashi InfoComm Limited, is hereby awarded Positional Promotion and Single Increment on the existing salary. <br/><br/><strong><em>$employeeName's</strong></em> change in position shall be implemented <strong><em>w.e.f. $effectiveDate</strong></em> and details are mentioned below:";
                $content .= "<br/><table style='margin-top:10px;'>
                                    <tr>
                                        <th style='padding:0 4px 2px 4px;'>Old Position</th>
                                        <th style='padding:0 4px 2px 4px;'>New Position</th>
                                    </tr>
                                    <tr>
                                        <td style='padding:0 4px 2px 4px;'>$employeeDesignation</td>
                                        <td style='padding:0 4px 2px 4px;'>$newEmployeeDesignation</td>
                                    </tr>
                            </table>";
                $content .= "<br/><table style='margin-top:10px;'>
                                    <tr>
                                        <th style='padding:0 4px 2px 4px;'>Grade & Pay Scale</th>
                                        <th style='padding:0 4px 2px 4px;'>Basic Salary (Old)</th>
                                        <th style='padding:0 4px 2px 4px;'>Basic Salary (New)</th>
                                    </tr>
                                    <tr>
                                        <td style='padding:0 4px 2px 4px;'>$gradeStep ($payScale)</td>
                                        <td style='padding:0 4px 2px 4px;'><center>$basicPay</center></td>
                                        <td style='padding:0 4px 2px 4px;'><center>$newBasicPay</center></td>
                                    </tr>
                            </table>";
                $cc = "1. $hodLabel, $employeeDept<br/>
                       2. $employeeName<br/>
                       3. Office copy";
                break;
            case 2:
                $content = "In appreciation of your hard work, the management is pleased to grant you <strong><em>$paySlab</em></strong> special increments in the same grade. <br/><br/> Your revised Basic salary would be <strong><em>Nu. $newBasicPay</em></strong> from <strong><em>Nu. $basicPay</em></strong> per month with effect from <strong><em>$effectiveDate</em></strong>.<br/><br/>";
                $content .= "Other service condition would be as per the appointment order and service rules of the company. The management hopes that you will continue your good work in promotion the par excellence of working ethic in the company. <br/><br/> For Bhutan Carbide and Chemical Limited. <br/>";

                // $content = "In pursuant to the decision taken by the Management Committee during the <strong><em>$currentPmsNumber Performance Evaluation Meeting</strong></em> held on $evaluationMeetingDate, <strong><em>$employeeName</strong></em>, holding Citizenship Identity Card number <strong><em>$employeeCID</strong></em>, $employeeDesignation under $employeeDept, Tashi InfoComm Limited, is hereby awarded a Single Increment on the existing salary.<br/><br/>The Single Increment shall be implemented <strong><em>w.e.f. $effectiveDate</strong></em>. Salary and grade details are mentioned below:";
                // $content .= "<br/><table style='margin-top:10px;'>
                //                     <tr>
                //                         <th style='padding:0 4px 2px 4px;'>Grade & Pay Scale</th>
                //                         <th style='padding:0 4px 2px 4px;'>Basic Salary (Old)</th>
                //                         <th style='padding:0 4px 2px 4px;'>Basic Salary (New)</th>
                //                     </tr>
                //                     <tr>
                //                         <td style='padding:0 4px 2px 4px;'>$gradeStep ($payScale)</td>
                //                         <td style='padding:0 4px 2px 4px;'><center>$basicPay</center></td>
                //                         <td style='padding:0 4px 2px 4px;'><center>$newBasicPay</center></td>
                //                     </tr>
                //             </table>";
                // $cc = "1. $hodLabel, $employeeDept<br/>
                //        2. General Manager, Finance Department<br/>
                //        3. $employeeName<br/>
                //        4. Office copy";

                $cc = "1. President <br/>
                       2. Chief Executive (Management Rep.) <br/>
                       3. General Manager, Operation <br/>
                       4. HOD / Finance & Accounts <br/>
                       5. Personal file";
                break;
            case 3:
                $content = "In pursuant to the decision taken by the Management Committee during the <strong><em>$currentPmsNumber Performance Evaluation Meeting</strong></em> held on $evaluationMeetingDate, <strong><em>$employeeName</strong></em>, holding Citizenship Identity Card number <strong><em>$employeeCID</strong></em>, $employeeDesignation under $employeeDept, Tashi InfoComm Limited, is hereby awarded a Double Increment on the existing salary.<br/><br/>The Double Increment shall be implemented <strong><em>w.e.f. $effectiveDate</strong></em>. Salary and grade details are mentioned below:";
                $content .= "<br/><table style='margin-top:10px;'>
                                    <tr>
                                        <th style='padding:0 4px 2px 4px;'>Grade & Pay Scale</th>
                                        <th style='padding:0 4px 2px 4px;'>Basic Salary (Old)</th>
                                        <th style='padding:0 4px 2px 4px;'>Basic Salary (New)</th>
                                    </tr>
                                    <tr>
                                        <td style='padding:0 4px 2px 4px;'>$gradeStep ($payScale)</td>
                                        <td style='padding:0 4px 2px 4px;'><center>$basicPay</center></td>
                                        <td style='padding:0 4px 2px 4px;'><center>$newBasicPay</center></td>
                                    </tr>
                            </table>";
                $cc = "1. $hodLabel, $employeeDept<br/>
                       2. General Manager, Finance Department<br/>
                       3. $employeeName<br/>
                       4. Office copy";
                break;
            case 4:
                $content = "Considering the exemplary initiatives taken at work in your department, the management recognizes your sincere, dedicated service hard work and initiatives at work. In appreciation of your good conduct, the management is pleased to promote you to next higher level " . ($newEmployeeDesignation ? "as <strong><em>$newEmployeeDesignation &nbsp;($newGradeStep)</em></strong>." : "to <strong><em>($newGradeStep)</em></strong>.") . " <br/><br/> Your revised Basic Pay Salary would be <strong><em>Nu. $newBasicPay </em></strong> only in <strong><em>$newGradeStep</em></strong> in the pay scale of <strong><em>($newPayScale)</em></strong>, with effect from <strong><em>$effectiveDate</em></strong> <br/><br/>";
                $content .= "Other service condition would be as per the appointment order and service rules of the company. The management hopes that you will continue your good work in promotion the par excellence of work ethic in the company. <br/><br/> For Bhutan Carbide and Chemicals Limited. <br/>";

                // $content = "In pursuant to the decision taken by the Management Committee during the <strong><em>$currentPmsNumber Performance Evaluation Meeting</strong></em> held on $evaluationMeetingDate, <strong><em>$employeeName</strong></em>, holding Citizenship Identity Card number <strong><em>$employeeCID</strong></em>, $employeeDesignation under $employeeDept, Tashi InfoComm Limited, is hereby awarded a Single Grade Promotion and shall be promoted from $pronoun current grade $gradeStep to $newGradeStep.<br/><br/>$employeeName" . "’s promotion shall be implemented <strong><em>w.e.f. $effectiveDate</strong></em>. Salary and grade details are mentioned below:";
                // $content .= "<br/><table style='margin-top:10px;'>
                //                     <tr>
                //                         <th style='padding:0 4px 2px 4px;'>Grade & Pay Scale (Old)</th>
                //                         <th style='padding:0 4px 2px 4px;'>Grade & Pay Scale (New)</th>
                //                         <th style='padding:0 4px 2px 4px;'>Basic Salary (Old)</th>
                //                         <th style='padding:0 4px 2px 4px;'>Basic Salary (New)</th>
                //                     </tr>
                //                     <tr>
                //                         <td style='padding:0 4px 2px 4px;'>$gradeStep ($payScale)</td>
                //                         <td style='padding:0 4px 2px 4px;'>$newGradeStep ($newPayScale)</td>
                //                         <td style='padding:0 4px 2px 4px;'><center>$basicPay</center></td>
                //                         <td style='padding:0 4px 2px 4px;'><center>$newBasicPay</center></td>
                //                     </tr>
                //             </table>";
                // $cc = "1. $hodLabel, $employeeDept<br/>
                //        2. General Manager, Finance Department<br/>
                //        3. $employeeName<br/>
                //        4. Office copy";

                $cc = "1. President <br/>
                       2. Chief Executive (Management Rep.) <br/>
                       3. General Manager, Operation <br/>
                       4. HOD / Finance & Accounts <br/>
                       5. Personal file";
                break;
            case 5:
                $content = "In pursuant to the decision taken by the Management Committee during the <strong><em>$currentPmsNumber Performance Evaluation Meeting</strong></em> held on $evaluationMeetingDate, <strong><em>$employeeName</strong></em>, holding Citizenship Identity Card number <strong><em>$employeeCID</strong></em>, $employeeDesignation under $employeeDept, Tashi InfoComm Limited, is hereby awarded a Double Grade Promotion and shall be promoted from $pronoun current grade $gradeStep to $newGradeStep.<br/><br/>$employeeName" . "’s promotion shall be implemented <strong><em>w.e.f. $effectiveDate</strong></em>. Salary and grade details are mentioned below:";
                $content .= "<br/><table style='margin-top:10px;'>
                                    <tr>
                                        <th style='padding:0 4px 2px 4px;'>Grade & Pay Scale (Old)</th>
                                        <th style='padding:0 4px 2px 4px;'>Grade & Pay Scale (New)</th>
                                        <th style='padding:0 4px 2px 4px;'>Basic Salary (Old)</th>
                                        <th style='padding:0 4px 2px 4px;'>Basic Salary (New)</th>
                                    </tr>
                                    <tr>
                                        <td style='padding:0 4px 2px 4px;'>$gradeStep ($payScale)</td>
                                        <td style='padding:0 4px 2px 4px;'>$newGradeStep ($newPayScale)</td>
                                        <td style='padding:0 4px 2px 4px;'><center>$basicPay</center></td>
                                        <td style='padding:0 4px 2px 4px;'><center>$newBasicPay</center></td>
                                    </tr>
                            </table>";
                $cc = "1. $hodLabel, $employeeDept<br/>
                       2. General Manager, Finance Department<br/>
                       3. $employeeName<br/>
                       4. Office copy";
                break;
            case 13:
                $cc = "1. TICL Management Committee members, for information<br/>
                       2. $hodLabel, $employeeDept for necessary action<br/>
                       3. Executive Manager, Human Resource and Administration Department for record and necessary action<br/>
                       4. $employeeName, for information<br/>";
                break;
            case 12:
                $prepend = "<br/><strong>Mr./Ms.:</strong> $employeeName <br/>
                                <strong>Citizenship Identity Card Number:</strong> $employeeCID <br/>
                                <strong>Designation:</strong> $employeeDesignation <br/>
                                <strong>Section:</strong> $employeeSection <br/>
                                <strong>Department:</strong> $employeeDept <br/>
                                <strong>Tashi InfoComm Limited</strong>";
                $prepend .= "<h3><center><strong><u>SUBJECT:	LETTER ASKING IMPROVEMENT</u></strong></center></h3>";
                $content = $prepend . $content;
                $cc = "1. $hodLabel, $employeeDept for necessary action<br/>
                       2. Office Copy";
                $notOrder = true;
                break;
            case 10:
                $prepend = "<br/><strong>Mr./Ms.:</strong> $employeeName <br/>
                                <strong>Citizenship Identity Card Number:</strong> $employeeCID <br/>
                                <strong>Designation:</strong> $employeeDesignation <br/>
                                <strong>Section:</strong> $employeeSection <br/>
                                <strong>Department:</strong> $employeeDept <br/>
                                <strong>Tashi InfoComm Limited</strong>";
                $prepend .= "<h3><center><strong><u>LETTER OF IMPROVEMENT</u></strong></center></h3>";
                $content = $prepend . $content;
                $cc = "1. $hodLabel, $employeeDept for necessary action<br/>
                       2. Executive Manager, Human Resource and Administration <br/>
                       3. Office Copy";
                $notOrder = true;
                break;
            case 11:
                $content = "The Management of Tashi InfoComm Limited would like to award this Letter of Appreciation in pursuant to the decision taken during the <strong><em>$currentPmsNumber Performance Evaluation Meeting</strong></em> held on $evaluationMeetingDate. The letter represents deep appreciation and recognition of your hard work and contribution towards the company in the last six months between $startingDateOfLastPMS and $endingDateOfLastPMS.<br/><br/>You have exhibited commitment, honesty and sincerity. Your hard work and all the extra efforts that you have put forth has not gone unnoticed. The Management is happy with your performance and contribution to the company.<br/><br/>Keep on contributing to the company’s growth in the future too.<br/><br/>All the best";
                $prepend = "<br/><strong>Mr./Ms.:</strong> $employeeName <br/>
                                <strong>Citizenship Identity Card Number:</strong> $employeeCID <br/>
                                <strong>Designation:</strong> $employeeDesignation <br/>
                                <strong>Section:</strong> $employeeSection <br/>
                                <strong>Department:</strong> $employeeDept <br/>
                                <strong>Tashi InfoComm Limited</strong>";
                $prepend .= "<h3><center><strong><u>LETTER OF APPRECIATION</u></strong></center></h3>";
                $content = $prepend . $content;
                $cc = "1. $hodLabel, $employeeDept for necessary action<br/>
                       2. Office Copy";
                $notOrder = true;
                break;
            case 8:
                $prepend = "<br/><strong>Mr./Ms.:</strong> $employeeName <br/>
                                <strong>Citizenship Identity Card Number:</strong> $employeeCID <br/>
                                <strong>Designation:</strong> $employeeDesignation <br/>
                                <strong>Section:</strong> $employeeSection <br/>
                                <strong>Department:</strong> $employeeDept <br/>
                                <strong>Tashi InfoComm Limited</strong>";
                $prepend .= "<h3><center><strong><u>SUBJECT:	LETTER OF LAST WARNING</u></strong></center></h3>";
                $content = $prepend . $content;
                $cc = "1. $hodLabel, $employeeDept for necessary action<br/>
                       2. Office Copy";
                $notOrder = true;
                break;
            default:
                abort(404);
        endswitch;
        if ($request->has('html')) {
            return view('printpages.officeorder', ['notOrder' => $notOrder, 'cc' => $cc, 'content' => $content, 'referenceNo' => $referenceNo, 'date' => $date, 'employeeName' => $employeeName, 'employeeEmpId' => $employeeEmpId, 'newEmployeeDesignation' => $newEmployeeDesignation, 'pmsOutcomeId' => $pmsOutcomeId, 'employeeDesignation' => $employeeDesignation]);
        }

        $pdf = PDF::loadView('printpages.officeorder', ['notOrder' => $notOrder, 'cc' => $cc, 'content' => $content, 'referenceNo' => $referenceNo, 'date' => $date, 'employeeName' => $employeeName, 'employeeEmpId' => $employeeEmpId, 'newEmployeeDesignation' => $newEmployeeDesignation, 'pmsOutcomeId' => $pmsOutcomeId, 'employeeDesignation' => $employeeDesignation]);
        $pdf->set_option('isRemoteEnabled', TRUE);
        $content = $pdf->download()->getOriginalContent();
        $path = 'public/officeorders/' . date('Y') . "/Office Order for $employeeName _ $employeeEmpId _ " . date('Y_m_d') . ".pdf";
        DB::table('pms_submission')->where('Id', $id)->update(['OfficeOrderEmailed' => 0, 'OfficeOrderPath' => "storage/officeorders/" . date('Y') . "/Office Order for $employeeName _ $employeeEmpId _ " . date('Y_m_d') . ".pdf"]);
        Storage::put($path, $content);
        return redirect('generateofficeorder')->with('employeeDeptId', $employeeDeptId)->with('successmessage', "Office order for $employeeName has been generated");
    }

    public function emailOfficeOrder(Request $request): \Illuminate\Http\RedirectResponse
    {
        $filePath = $request->file;
        $empId = $request->empId;
        $empEmailAddress = $request->email;
        $backVar = "successmessage";
        if ($filePath) {
            $empDetails = DB::table('mas_employee')->where('Id', $empId)->get(["Email", "Name", "EmpId"]);
            $empName = $empDetails[0]->Name;
            $employeeId = $empDetails[0]->EmpId;
            $empPMSOutcomeQuery = DB::select("select T1.Id,T2.Name as Outcome, T1.SubmissionTime from pms_submission T1 join mas_pmsoutcome T2 on T1.PMSOutcomeId = T2.Id where T1.EmployeeId = ? order by T1.SubmissionTime DESC limit 1", [$empId]);
            $submissionId = $empPMSOutcomeQuery[0]->Id;
            $empPMSOutcome = $empPMSOutcomeQuery[0]->Outcome;
            $submissionTime = $empPMSOutcomeQuery[0]->SubmissionTime;
            $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', $submissionTime)->orderBy('StartDate', 'DESC')->pluck('PMSNumber');
            $currentPMSNumber = $currentPMSQuery[0];
            if ($empEmailAddress && $empEmailAddress != '@tashicell.com') {
                $env = (config('app.env'));
                // if ($env == "local") {
                $filePath = str_replace("https://bod.tashicell.com/storage/officeorders", "storage/officeorders", $filePath);
                // }
                $this->sendMail($empEmailAddress, "Dear $empName, <br/> Please find attached your Office Order for <strong>$empPMSOutcome</strong> for the PMS Round $currentPMSNumber", "Office Order for PMS Round $currentPMSNumber", null, null, $filePath);
                DB::table('pms_submission')->where('Id', $submissionId)->update(['OfficeOrderEmailed' => 1]);
                $this->saveAuditTrail('pms_submission', $submissionId);
                $message = "Office Order for $empName (Emp Id: $employeeId) emailed successfully!";
            } else {
                $backVar = "errormessage";
                $message = "Office Order for $empName (Emp Id: $employeeId) could not be emailed. Please update email address of employee.";
            }
        }
        return back()->with($backVar, $message);
    }

    public function saveAppraisee(Request $request): \Illuminate\Http\JsonResponse
    {
        //CHECK IF ALREADY SAVED
        $employeeId = Auth::id();
        $today = strtotime(date('Y-m-d'));
        $pmsCount = 0;
        $lastPMSId = '';
        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-09-31'))) {
            $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-07-01'), date('Y-09-31')])->count();
            $lastPMSQuery = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-07-01'), date('Y-09-31')])->pluck('Id');
            $lastPMSId = isset($lastPMSQuery[0]) ? $lastPMSQuery[0] : '';
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-05-31'))) {
                $pmsCount = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-01-01'), date('Y-05-31')])->count();
                $lastPMSQuery = DB::table('pms_submission')->where('EmployeeId', Auth::id())->whereRaw("(DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= ?)", [date('Y-01-01'), date('Y-05-31')])->pluck('Id');
                $lastPMSId = isset($lastPMSQuery[0]) ? $lastPMSQuery[0] : '';
            }
        }

        $inputs = $request->except(['pmssubmissiondetail', 'File']);

        if ($pmsCount > 0 && !$inputs['Id']) {
            return response()->json(['success' => true, 'message' => 'PMS has been saved!', 'Id' => $lastPMSId]);
        }

        $file = $request->file('File');
        $file2 = $request->file('File2');
        $details = $request->input('pmssubmissiondetail');

        if ($file) {
            $directory = 'uploads/' . date('Y') . '/' . date('m');
            $fileName = 'PMS File_' . Auth::user()->EmpId . '_' . randomString() . randomString() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $inputs['FilePath'] = $directory . '/' . $fileName;
        }

        if ($file2) {
            $directory = 'uploads/' . date('Y') . '/' . date('m');
            $extension2 = $file2->getClientOriginalExtension();
            if (!$this->in_arrayi($extension2, ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
                return response()->json(['success' => false, 'message' => 'Wrong file format. Permitted file formats are image files or excel or word documents']);
            }
            $fileName2 = 'PMS Additional Document_' . Auth::user()->EmpId . '_' . randomString() . randomString() . '.' . $file2->getClientOriginalExtension();
            $file2->move($directory, $fileName2);
            $inputs['File2Path'] = $directory . '/' . $fileName2;
        }

        DB::beginTransaction();
        try {
            $inputs['EmployeeId'] = Auth::id();
            $inputs['SectionId'] = Auth::user()->SectionId;
            $inputs['SubmissionTime'] = date('Y-m-d H:i:s');

            if ($inputs['Id']) {
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $updateObject = PMSSubmission::find($inputs['Id']);
                $updateObject->fill($inputs);
                $updateObject->update();

                $lastStatusQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $inputs['Id'])->pluck('LastStatusId');
                $lastStatusId = $lastStatusQuery[0];

                if ($lastStatusId != CONST_PMSSTATUS_DRAFT) {
                    $this->saveStatus($inputs['Id'], CONST_PMSSTATUS_DRAFT, Auth::id());
                }
            } else {
                if (!$file) {
                    $inputs['FilePath'] = NULL;
                }
                if (!$file2) {
                    $inputs['File2Path'] = NULL;
                }

                $empDetails = DB::table('mas_employee as T1')->join('mas_designation as A', 'A.Id', '=', 'T1.DesignationId')->join('mas_gradestep as T2', 'T2.Id', '=', 'T1.GradeStepId')->leftJoin('mas_section as T3', 'T3.Id', '=', 'T1.SectionId')->join('mas_department as T4', 'T4.Id', '=', 'T1.DepartmentId')->where('T1.Id', Auth::id())->get(array("T1.BasicPay", "T2.Name as GradeStep", "T3.Name as Section", "T4.Name as Department", "T2.PayScale", "T1.GradeStepId", "A.Name as Designation"));

                $inputs['BasicPay'] = $empDetails[0]->BasicPay;
                $inputs['PayScale'] = $empDetails[0]->PayScale;
                $inputs['GradeStepId'] = $empDetails[0]->GradeStepId;

                $inputs['CreatedBy'] = Auth::id();
                $inputs['Id'] = UUID();
                PMSSubmission::create($inputs);
                $this->saveStatus($inputs['Id'], CONST_PMSSTATUS_DRAFT, Auth::id());
            }

            foreach ($details as $detail):
                if (isset($detail['Id']) && $detail['Id']) {
                    $detailObject = PMSSubmissionDetail::find($detail['Id']);
                    $detail['updated_at'] = date('Y-m-d H:i:s');
                    $detail['EditedBy'] = Auth::id();
                    if ($detail['SelfRating'] === '') {
                        $detail['SelfRating'] = NULL;
                    }
                    $detailObject->fill($detail);
                    $detailObject->update();
                } else {
                    $detail['Id'] = UUID();
                    $detail['SubmissionId'] = $inputs['Id'];
                    if ($detail['SelfRating'] === '') {
                        $detail['SelfRating'] = NULL;
                    }
                    $detail['CreatedBy'] = Auth::id();
                    PMSSubmissionDetail::create($detail);
                }
            endforeach;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return response()->json(['success' => false, 'message' => 'PMS could not be saved!']);
        }
        DB::commit();
        return response()->json(['success' => true, 'message' => 'PMS has been saved!', 'Id' => $inputs['Id']]);
    }

    public function saveAppraiser(Request $request): \Illuminate\Http\JsonResponse
    {
        $inputs = $request->input('pmssubmissiondetail');
        $directory = 'uploads/' . date('Y') . '/' . date('m');
        $remarks = $request->Remarks ?: NULL;

        DB::beginTransaction();
        try {
            foreach ($inputs as $key => $value):
                $id = $value['Id'];
                if (isset($value['Level1Rating']) && $value['Level1Rating'] === '') {
                    $value['Level1Rating'] = NULL;
                }
                if (isset($value['Level2Rating']) && $value['Level2Rating'] === '') {
                    $value['Level2Rating'] = NULL;
                }
                $object = PMSSubmissionDetail::find($id);
                $object->fill($value);
                $object->update();
            endforeach;

            $submissionIdQuery = DB::table('pms_submissiondetail')->where('Id', $id)->pluck('SubmissionId');
            $pmsSubmissionId = $submissionIdQuery[0];
            $lastStatusQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $pmsSubmissionId)->pluck('LastStatusId');
            $lastStatusId = $lastStatusQuery[0];

            $empIdQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $pmsSubmissionId)->pluck('EmpId');
            $empId = $empIdQuery[0];

            $file3 = $request->file('File3');
            $file4 = $request->file('File4');
            if ($file3) {
                $fileName = 'PMS File_' . $empId . '_2_' . randomString() . randomString() . '.' . $file3->getClientOriginalExtension();
                $file3->move($directory, $fileName);
                $filePath = $directory . '/' . $fileName;
                DB::table('pms_submission')->where('Id', $pmsSubmissionId)->update(['File3Path' => $filePath]);
            }
            if ($file4) {
                $fileName = 'PMS File_' . $empId . '_3_' . randomString() . randomString() . '.' . $file4->getClientOriginalExtension();
                $file4->move($directory, $fileName);
                $filePath = $directory . '/' . $fileName;
                DB::table('pms_submission')->where('Id', $pmsSubmissionId)->update(['File4Path' => $filePath]);
            }

            if ($lastStatusId != CONST_PMSSTATUS_DRAFT) {
                $this->saveStatus($pmsSubmissionId, CONST_PMSSTATUS_DRAFT, Auth::id(), $remarks);
            } else {
                DB::table('viewpmssubmissionwithlaststatus')->where('Id', $pmsSubmissionId)->update(['LastRemarks' => $remarks]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return response()->json(['success' => false, 'message' => 'PMS could not be saved!']);
        }
        DB::commit();
        return response()->json(['success' => true, 'message' => 'PMS has been saved!']);
    }

    public function finalAdjustment(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $targetRevenue = $request->TargetRevenue;
        $achievedRevenue = $request->AchievedRevenue;
        $finalAdjustmentPercentage = $request->FinalAdjustmentPercent;

        $adjustmentMarks = doubleval($achievedRevenue) / doubleval($targetRevenue) * $finalAdjustmentPercentage;

        if ($adjustmentMarks > $finalAdjustmentPercentage) {
            $adjustmentMarks = $finalAdjustmentPercentage;
        }

        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        DB::table('sys_pmsnumber')->where('Id', $pmsId)->update(['TargetRevenue' => $targetRevenue, 'AchievedRevenue' => $achievedRevenue]);

        $today = strtotime(date('Y-m-d'));
        if ($today >= strtotime(date('Y-07-01'))) {
            $id = DB::table('mas_pmssettings')->whereRaw('created_at >= ?', [date('Y-07-01 00:00:00')])->pluck('Id');
            $fromDate = date('Y-07-01 00:00:00');
            $toDate = date('Y-09-31 23:59:59');
        } else {
            $id = DB::table('mas_pmssettings')->whereRaw('created_at >= ? and created_at <= ?', [date('Y-01-01 00:00:00'), date('Y-05-31 23:59:59')])->pluck('Id');
            $fromDate = date('Y-01-01 00:00:00');
            $toDate = date('Y-05-31 23:59:59');
        }

        if (isset($id) && $id) {
            DB::table('mas_pmssettings')->where('Id', $id)->update(['FinalAdjustmentPercent' => $finalAdjustmentPercentage, 'EditedBy' => Auth::id(), 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            DB::table('mas_pmssettings')->insert(['Id' => UUID(), 'FinalAdjustmentPercent' => $finalAdjustmentPercentage, 'CreatedBy' => Auth::id(), 'created_at' => date('Y-m-d H:i:s')]);
        }

        DB::statement("call ComputeFinalScore(?,?,?,?)", [$fromDate, $toDate, $adjustmentMarks, $finalAdjustmentPercentage]);
        $pmsHistoricals = DB::table('pms_historical')->where('PMSNumberId', $pmsId)->get(['Id', 'PMSSubmissionId']);
        foreach ($pmsHistoricals as $pmsHistorical):
            $id = $pmsHistorical->Id;
            $submissionId = $pmsHistorical->PMSSubmissionId;
            $finalScore = $this->getFinalScore($submissionId);
            DB::table('pms_historical')->where('Id', $id)->update(['PMSScore' => $finalScore]);
        endforeach;
        return redirect('appraisepms')->with('successmessage', $finalAdjustmentPercentage . "% revenue score adjustment has been applied to all employees' PMS Scores.");
    }

    public function formatDate($date): string
    {
        $date = date_create($date);
        $day = date_format($date, 'j');
        $suffix = date_format($date, 'S');
        $month = date_format($date, 'F');
        $year = date_format($date, 'Y');
        return "$day<sup>$suffix</sup> of $month $year";
    }

    public function formatDateNoOf($date): string
    {
        $date = date_create($date);
        $day = date_format($date, 'j');
        $suffix = date_format($date, 'S');
        $month = date_format($date, 'F');
        $year = date_format($date, 'Y');

        return "$day<sup>$suffix</sup> $month $year";
    }

    public function getClose(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [date('Y-m-d')]);
        $pmsId = $pmsNumber[0]->Id;
        $currentPMSRound = $pmsNumber[0]->PMSNumber;

        $status = DB::table('sys_pmsnumber')->where('Id', $pmsId)->pluck('Status');
        return view('application.closepms')->with('currentPMSRound', $currentPMSRound)->with('status', $status[0]);
    }

    public function postClose(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $type = $request->input('type');

        if (!in_array($type, [2, 3])) {
            abort(404);
        }

        $currentPMSSubmissionDateRaw = DB::table('pms_submission')->max('SubmissionTime');
        $currentPMSSubmissionDate = date_format(date_create($currentPMSSubmissionDateRaw), 'Y-m-d');

        $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$currentPMSSubmissionDate]);
        $pmsId = $pmsNumber[0]->Id;
        $currentPMSRound = $pmsNumber[0]->PMSNumber;

        DB::update("UPDATE sys_pmsnumber T1 SET T1.Status = ? where T1.Id = ?", [$type, $pmsId]);

        $this->saveAuditTrail('sys_pmsnumber', $pmsId);
        return redirect('closepms')->with('successmessage', "PMS Round $currentPMSRound has been closed");
    }

    public function getOpenPMS(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [date('Y-m-d')]);
        $pmsId = $pmsNumber[0]->Id;
        $currentPMSRound = $pmsNumber[0]->PMSNumber;

        $status = DB::table('sys_pmsnumber')->where('Id', $pmsId)->pluck('Status');
        return view('application.openpms')->with('currentPMSRound', $currentPMSRound)->with('status', $status[0]);
    }

    public function openPMS(Request $request): \Illuminate\Http\RedirectResponse
    {
        $type = $request->input('type');

        if (!in_array($type, [1, 2])) {
            abort(404);
        }

        $currentPMSSubmissionDateRaw = DB::table('pms_submission')->max('SubmissionTime');
        $currentPMSSubmissionDate = date_format(date_create($currentPMSSubmissionDateRaw), 'Y-m-d');

        $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$currentPMSSubmissionDate]);
        $pmsId = $pmsNumber[0]->Id;

        DB::update("UPDATE sys_pmsnumber T1 SET T1.Status = ? where T1.Id = ?", [$type, $pmsId]);
        $this->saveAuditTrail('sys_pmsnumber', $pmsId);
        return back();
    }

    public function checkAllPMSSubmitted(): int
    {
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['StartDate']);
        $currentPMSStartDate = $statusQuery[0]->StartDate;

        $pendingOrUnsubmittedPMSQuery = DB::select("select count(T1.Id) as PendingUnsubmittedCount from mas_employee T1 left join viewpmssubmissionwithlaststatus T2 on T2.EmployeeId = T1.Id and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and T2.LastStatusId = ? where coalesce(T1.Status,0) = 1 and T1.RoleId <> 1 and coalesce(T1.PositionId,'eeee') <> ? and T2.Id is null", [$currentPMSStartDate, CONST_PMSSTATUS_APPROVED, CONST_POSITION_MD]);
        $count = !isset($pendingOrUnsubmittedPMSQuery[0]) ? 0 : $pendingOrUnsubmittedPMSQuery[0]->PendingUnsubmittedCount;
        return 0;
        return $count;
    }

    public function checkAllPMSCompleted(): int
    {
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['StartDate']);
        $currentPMSStartDate = $statusQuery[0]->StartDate;

        $pendingOrUnsubmittedPMSQuery = DB::select("select count(T1.Id) as PendingUnsubmittedCount from mas_employee T1 left join viewpmssubmissionwithlaststatus T2 on T2.EmployeeId = T1.Id and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? and T2.PMSOutcomeId is not null where coalesce(T1.Status,0) = 1 and T1.RoleId <> 1 and coalesce(T1.PositionId,'eeee') <> ? and T2.Id is null", [$currentPMSStartDate, CONST_POSITION_MD]);
        $count = !isset($pendingOrUnsubmittedPMSQuery[0]) ? 0 : $pendingOrUnsubmittedPMSQuery[0]->PendingUnsubmittedCount;
        return 0;
        return $count;
    }

    public function checkAllPMSHasFinalScore(): int
    {
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('Id');
        $pmsId = $currentPMSQuery[0];
        $statusQuery = DB::table('sys_pmsnumber')->where('Id', $pmsId)->get(['StartDate']);
        $currentPMSStartDate = $statusQuery[0]->StartDate;

        $pendingOrUnsubmittedPMSQuery = DB::select("select count(T1.Id) as PendingUnsubmittedCount from mas_employee T1 left join (viewpmssubmissionwithlaststatus T2 join pms_submissionfinalscore T3 on T3.SubmissionId = T2.Id) on T2.EmployeeId = T1.Id and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= ? where coalesce(T1.Status,0) = 1 and T1.RoleId <> 1 and coalesce(T1.PositionId,'eeee') <> ? and T3.Id is null", [$currentPMSStartDate, CONST_POSITION_MD]);
        $count = !isset($pendingOrUnsubmittedPMSQuery[0]) ? 0 : $pendingOrUnsubmittedPMSQuery[0]->PendingUnsubmittedCount;
        return 0;
        return $count;
    }

    public function saveOutcome(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $ids = $request->input('Id');
        $outcomeIds = $request->input('OutcomeId');
        $submissionIds = $request->input('SubmissionId');
        $submitType = $request->input("SubmitType");
        $deletedIds = $request->input("DeletedEmployeeIds");
        $draft = ($submitType == 1) ? 1 : 0;
        foreach ($ids as $key => $id):
            $outcomeId = $outcomeIds[$key];
            $submissionId = $submissionIds[$key];
            $outcomeName = DB::table('mas_pmsoutcome')->where('Id', $outcomeId)->pluck('Name');
            if (isset($outcomeName[0])) {
                $outcomeName = $outcomeName[0];
            } else {
                $outcomeName = NULL;
            }
            DB::table('pms_submission')->where('Id', $submissionId)->update(['PMSOutcomeId' => NULL, 'PMSOutcomeDraft' => $draft, 'SavedPMSOutcomeId' => $outcomeId]);
            DB::table('pms_historical')->where('PMSSubmissionId', $submissionId)->update(['PMSResult' => $outcomeName]);
        endforeach;
        $deletedIdsArray = explode(",", $deletedIds);
        if (count($deletedIdsArray) > 0) {
            $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('StartDate');
            $pmsStartDate = $currentPMSQuery[0];
            DB::table("pms_submission")->where("SubmissionTime", ">=", $pmsStartDate)->whereIn("EmployeeId", $deletedIdsArray)->update(['SavedPMSOutcomeId' => 1, 'PMSOutcomeDraft' => 1]);
        }
        return redirect('pmsscorereport')->with('successmessage', 'Result has been saved');
    }

    public function getFinalScore($id): float
    {
        $application = DB::select("select T1.Id,T1.NewPayScale,T1.FilePath,T1.File2Path,T1.File3Path,T1.File4Path,T1.NewDesignationId,T1.NewGradeId,T1.NewLocation,T1.NewBasicPay,T1.NewGradeStepId,T1.NewSupervisorId,coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) as PMSOutcomeId, T5.HasBasicPayChange, T5.HasDesignationAndLocationChange, T5.HasPayChange, T5.HasPositionChange, T1.FinalRemarks, T1.OutcomeDateTime,T1.EmployeeId,T1.WeightageForLevel1, T1.Level2CriteriaType,T1.WeightageForLevel2, A.Name as Level1Employee, B.Name as Level2Employee from viewpmssubmissionwithlaststatus T1 join (mas_hierarchy T2 join (mas_employee T3 join mas_position A on A.Id = T3.PositionId) on T2.ReportingLevel1EmployeeId = T3.Id left join (mas_employee T4 join mas_position B on B.Id = T4.PositionId) on T4.Id = T2.ReportingLevel2EmployeeId) on T2.EmployeeId = T1.EmployeeId left join mas_pmsoutcome T5 on T5.Id = coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) where T1.Id = ? and T1.LastStatusId = ?", [$id, CONST_PMSSTATUS_APPROVED]);
        if (count($application) == 0) {
            abort(404);
        }
        $applicationDetails = DB::select("select T2.AssessmentArea, T2.ApplicableToLevel2,T2.Weightage, T2.SelfRating, T2.Level1Rating, T2.Level2Rating from viewpmssubmissionwithlaststatus T1 join pms_submissiondetail T2 on T2.SubmissionId = T1.Id where T1.Id = ?", [$id]);
        $finalScore = DB::table('pms_submissionfinalscore')->where('SubmissionId', $id)->pluck('FinalScore');
        if (count($finalScore)):
            $finalScore = $finalScore[0];
        else:
            $finalScore = '';
        endif;

        $appraisalType = '';
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
        $level1WeightedTotal = $level2WeightedTotal = $selfRatingTotal = $level1QualitativeTotal = $level1QuantitativeTotal = $level2QualitativeTotal = $level2QuantitativeTotal = $level1RatingTotal = $level2RatingTotal = $qualitativeWeightageTotal = $quantitativeWeightageTotal = 0;
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

        if ($type == 1):
            if ($finalAdjustmentPercentDetails):
                $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal;
            endif;
            // $level1WeightedTotal = $level1RatingTotal / 100 * $application[0]->WeightageForLevel1;
            $level1WeightedTotal = $level1RatingTotal / $quantitativeWeightageTotal * $application[0]->WeightageForLevel1;
            if ($finalAdjustmentPercentDetails):
                // $level1AdjustedTotal = round($adjustedLevel1Score, 2) / 100 * $application[0]->WeightageForLevel1;
                $level1AdjustedTotal = round($adjustedLevel1Score, 2) / $quantitativeWeightageTotal * $application[0]->WeightageForLevel1;
            endif;

            if ($finalAdjustmentPercentDetails):
                $adjustedLevel2Score = ($level2QualitativeTotal / $qualitativeWeightageTotal * ($qualitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level2QuantitativeTotal;
            endif;
            // $level2WeightedTotal = $level2RatingTotal / 100 * $application[0]->WeightageForLevel2;
            $level2WeightedTotal = $level2RatingTotal / $qualitativeWeightageTotal * $application[0]->WeightageForLevel2;
            if ($finalAdjustmentPercentDetails):
                // $level2AdjustedTotal = round($adjustedLevel2Score, 2) / 100 * $application[0]->WeightageForLevel2;
                $level2AdjustedTotal = round($adjustedLevel2Score, 2) / $qualitativeWeightageTotal * $application[0]->WeightageForLevel2;
            endif;

            $finalScore = $finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2) + round($level2AdjustedTotal, 2)) : (round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2));
        elseif ($type == 2):
            if ($finalAdjustmentPercentDetails):
                $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal;
            endif;
            // $level1WeightedTotal = $level1RatingTotal / 100 * $application[0]->WeightageForLevel1;
            $level1WeightedTotal = $level1RatingTotal / $quantitativeWeightageTotal * $application[0]->WeightageForLevel1;
            if ($finalAdjustmentPercentDetails):
                // $level1AdjustedTotal = round($adjustedLevel1Score, 2) / 100 * $application[0]->WeightageForLevel1;
                $level1AdjustedTotal = round($adjustedLevel1Score, 2) / $quantitativeWeightageTotal * $application[0]->WeightageForLevel1;
            endif;

            $level2WeightedTotal = $level2RatingTotal / $qualitativeWeightageTotal * $application[0]->WeightageForLevel2;

            $finalScore = round($finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2) + round($level2WeightedTotal, 2)) : (round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2)), 2);
        else:
            if ($finalAdjustmentPercentDetails):
                $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal;
            endif;
            $level1WeightedTotal = $level1RatingTotal / 100 * $application[0]->WeightageForLevel1;
            // $level1WeightedTotal = $level1RatingTotal / $quantitativeWeightageTotal * $application[0]->WeightageForLevel1;
            if ($finalAdjustmentPercentDetails):
                // $level1AdjustedTotal = round($adjustedLevel1Score, 2) / 100 * $application[0]->WeightageForLevel1;
                $level1AdjustedTotal = round($adjustedLevel1Score, 2) / $quantitativeWeightageTotal * $application[0]->WeightageForLevel1;
            endif;

            $finalScore = round($finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2)) : (round($level1WeightedTotal, 2)), 2);
        endif;

        return round($finalScore, 2);
    }

    public function getFinalScore2($id) {}

    public function postProcessMultiple(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $currentPMSId = $this->getCurrentRound();
        $secondLevelEmailArray = [];
        $secondLevelMobileNoArray = [];
        $directory = 'uploads/' . date('Y') . '/' . date('m');
        $topArrayId = false;
        $loggedInEmployeeId = Auth::id();

        $id = $request->Id;
        $saveType = $request->SaveType; // 1 for Appraised, 2 for Saved
        $remarks = $request->Remarks;
        $file3 = $request->File3;
        $file4 = $request->File4;

        $appraiserDetails = $this->getAppraiserDetails(false, $id, $loggedInEmployeeId);
        $level = $appraiserDetails['appraiserLevel'];

        if (!in_array($saveType, [1, 2])) {
            $saveType = 1;
        }
        if (!in_array($level, [1, 2])) {
            abort(404);
        }

        $statusDetails = DB::select("select T1.Id, T1.SubmissionTime, T1.EmployeeId, T2.EmpId,T2.Name as EmployeeName, concat(T2.Name,' of ', T3.Name) as Employee, Z1.Name as GradeStep, T4.Name as Position from pms_submission T1 join (mas_employee T2 join mas_gradestep Z1 on Z1.Id = T2.GradeStepId join mas_department T3 on T3.Id = T2.DepartmentId left join mas_position T4 on T4.Id = T2.PositionId) on T2.Id = T1.EmployeeId where T1.Id = ?", [$id]);
        $employee = $statusDetails[0]->EmployeeName;
        $employeeId = $statusDetails[0]->EmployeeId;
        $empIdQuery = DB::table('viewpmssubmissionwithlaststatus')->where('Id', $id)->pluck('EmpId');
        $empId = $empIdQuery[0];
        $pmssubmissiondetails = $request->pmssubmissiondetail;

        if ($file3) {
            if (!$this->in_arrayi($file3->getClientOriginalExtension(), ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
                return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files, Excel or Word documents');
            }
            $fileName = 'PMS File_' . $empId . '_2_' . randomString() . randomString() . '.' . $file3->getClientOriginalExtension();
            $file3->move($directory, $fileName);
            $filePath = $directory . '/' . $fileName;
        }
        if ($file4) {
            if (!$this->in_arrayi($file4->getClientOriginalExtension(), ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
                return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files, Excel or Word documents');
            }
            $fileName = 'PMS File_' . $empId . '_3_' . randomString() . randomString() . '.' . $file4->getClientOriginalExtension();
            $file4->move($directory, $fileName);
            $filePath = $directory . '/' . $fileName;
        }

        //COUNT LEVEL 2 APPRAISERS
        $hasLevel2Count = DB::table('mas_hierarchy')->whereNotNull('ReportingLevel2EmployeeId')->where('EmployeeId', $employeeId)->count();

        DB::beginTransaction();
        try {
            $check = DB::table('pms_submissionmultiple')->where('SubmissionId', $id)->where('AppraisedByEmployeeId', $loggedInEmployeeId)->pluck('Id');
            if (count($check) > 0) {
                $topArrayId = $check[0];
            }
            $topArray['Remarks'] = $remarks ?: NULL;
            if ($topArrayId) {
                $topArray['Status'] = $saveType;
                if (isset($filePath)) {
                    $topArray['FilePath'] = $filePath;
                }
                $topArray['EditedBy'] = $loggedInEmployeeId;
                $topArray['updated_at'] = date('Y-m-d H:i:s');
                $updateObject = PMSSubmissionMultiple::find($topArrayId);
                $updateObject->fill($topArray);
                $updateObject->update();
            } else {
                $topArray['Id'] = $topArrayId = UUID();
                $topArray['SubmissionId'] = $id;
                $topArray['AppraisedByEmployeeId'] = $loggedInEmployeeId;
                $topArray['Status'] = $saveType;
                $topArray['ForLevel'] = $level;
                if (isset($filePath)) {
                    $topArray['FilePath'] = $filePath;
                }
                $topArray['CreatedBy'] = $loggedInEmployeeId;
                PMSSubmissionMultiple::create($topArray);
            }

            PMSSubmissionMultipleDetail::where('SubmissionMultipleId', $topArrayId)->delete();
            foreach ($pmssubmissiondetails as $pmssubmissiondetail):
                $bottomId = $pmssubmissiondetail['Id'];
                $score = $pmssubmissiondetail['Level1Rating'] ?? $pmssubmissiondetail['Level2Rating'];
                $bottomArray['Id'] = UUID();
                $bottomArray['SubmissionMultipleId'] = $topArrayId;
                $bottomArray['SubmissionDetailId'] = $bottomId;
                $bottomArray['Score'] = ($score !== '') ? $score : NULL;
                $bottomArray['CreatedBy'] = $loggedInEmployeeId;
                PMSSubmissionMultipleDetail::create($bottomArray);
            endforeach;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->with('errormessage', $e->getMessage());
        }
        DB::commit();
        if ((int) $saveType == 1) {
            //SEND MAIL TO APPRAISEE AND NEXT LEVEL APPRAISER IF ANY
            $actionByNameQuery = DB::table('mas_employee as T1')->join('mas_designation as T2', 'T2.Id', '=', 'T1.DesignationId')->where('T1.Id', Auth::id())->get(['T1.Name', 'T1.Gender', 'T1.Email', 'T1.MobileNo', 'T2.Name as Designation']);
            $actionByName = isset($actionByNameQuery[0]) ? ($actionByNameQuery[0]->Name . " (" . $actionByNameQuery[0]->Designation . ")") : "";
            $actionByName = $actionByName ? ($actionByNameQuery[0]->Gender == 'M' ? 'Mr. ' : 'Ms. ') . $actionByName : '';
            $redirectLink = url('/') . "?redirect=trackpms";
            $employeeEmail = $actionByNameQuery[0]->Email;
            $employeeMobileNo = $actionByNameQuery[0]->MobileNo;

            $smsMessage = "Your PMS application has been Appraised by $actionByName. Please check your email for details.";
            $emailMessage = "Your PMS application has been Appraised by $actionByName.<br/><a href='$redirectLink'>Click here to track.</a>";

            if ($employeeEmail && $employeeEmail != '@tashicell.com') {
                $this->sendMail($employeeEmail, $emailMessage, "Your PMS Evaluation has been Appraised by $actionByName.");
            }
            if ($employeeMobileNo) {
                $this->sendSMS($employeeMobileNo, $smsMessage);
            }

            //END SEND MAIL

            //CALCULATE AVG AND PUSH TO MAIN TABLE
            $column = '';
            $scoreColumn = '';
            if ($level == 1) {
                $column = "ReportingLevel1EmployeeId";
                $scoreColumn = "Level1Rating";
                $status = CONST_PMSSTATUS_VERIFIED;
            } else {
                $column = 'ReportingLevel2EmployeeId';
                $scoreColumn = "Level2Rating";
                $status = CONST_PMSSTATUS_APPROVED;
            }

            $noOfAppraisers = DB::table('mas_hierarchy as T1')->where('T1.EmployeeId', $employeeId)->whereNotNull("$column")->count();
            $noSubmitted = DB::table('pms_submissionmultiple as T1')->where('T1.SubmissionId', $id)->whereRaw("T1.ForLevel = ?", $level)->where("Status", 1)->count();
            if ((int) $noOfAppraisers == (int) $noSubmitted) {
                if ($level == 1 && ($hasLevel2Count > 0)) {
                    $secondLevelEmployeeIdQuery = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.ReportingLevel2EmployeeId')->whereNotNull("T1.ReportingLevel2EmployeeId")->where('T1.EmployeeId', $employeeId)->get(['T2.Email', 'T2.MobileNo']);
                    if (count($secondLevelEmployeeIdQuery) > 0) {
                        foreach ($secondLevelEmployeeIdQuery as $secondLevelEmployee):
                            if ($secondLevelEmployee->Email && $secondLevelEmployee->Email != '@tashicell.com') {
                                $secondLevelEmailArray[] = $secondLevelEmployee->Email;
                            }
                            if ($secondLevelEmployee->MobileNo) {
                                $secondLevelMobileNoArray[] = $secondLevelEmployee->MobileNo;
                            }
                        endforeach;
                    }

                    $empDetails = DB::table('mas_employee as T1')->join('mas_designation as A', 'A.Id', '=', 'T1.DesignationId')->join('mas_gradestep as T2', 'T2.Id', '=', 'T1.GradeStepId')->leftJoin('mas_section as T3', 'T3.Id', '=', 'T1.SectionId')->join('mas_department as T4', 'T4.Id', '=', 'T1.DepartmentId')->where('T1.Id', $employeeId)->get(array("T1.BasicPay", "T1.Gender as EmployeeGender", "T1.Name as EmployeeName", "T1.CIDNo", "T2.Name as GradeStep", "T3.Name as Section", "T4.Name as Department", "T2.PayScale", "T1.GradeStepId", 'A.Name as Designation'));
                    $employeeGender = $empDetails[0]->EmployeeGender;
                    $employeeName = $empDetails[0]->EmployeeName;
                    $employeeName = (($employeeGender == 'M') ? 'Mr. ' : 'Ms. ') . $employeeName;
                    $employeeCIDNo = $empDetails[0]->CIDNo;
                    $designation = $empDetails[0]->Designation;
                    $department = $empDetails[0]->Department;
                    $section = $empDetails[0]->Section;

                    $redirectLink = url('/') . "?redirect=processpms/" . $id;
                    $smsMessage = "PMS of " . $employeeName . " (" . $employeeCIDNo . ") has been appraised and is at your desk. Please check your email for details.";
                    $emailMessage = "PMS of " . $employeeName . " ($designation) of $section, $department has been appraised and is at your desk. <br/><a href='$redirectLink'>Click here to evaluate.</a>";
                    if (count($secondLevelEmailArray) > 0) {
                        foreach ($secondLevelEmailArray as $secondLevelEmail):
                            $this->sendMail($secondLevelEmail, $emailMessage, "PMS of " . $employeeName . " has been appraised and is at your desk.");
                        endforeach;
                    }
                    if (count($secondLevelMobileNoArray) > 0) {
                        foreach ($secondLevelMobileNoArray as $secondLevelMobileNo):
                            $this->sendSMS($secondLevelMobileNo, $smsMessage);
                        endforeach;
                    }
                }

                $remarks = DB::select("select GROUP_CONCAT(concat(B.Name,' (',C.Name,'): ',A.Remarks) SEPARATOR '<br/>') as Remarks from pms_submissionmultiple A join (mas_employee B join mas_designation C on C.Id = B.DesignationId) on B.Id = A.AppraisedByEmployeeId where A.SubmissionId = ? and A.Remarks is not null and A.Remarks <> ''", [$id]);
                DB::update("UPDATE pms_submissiondetail T1 set T1.$scoreColumn = (SELECT AVG(A.Score) from pms_submissionmultipledetail A join pms_submissionmultiple B on B.Id = A.SubmissionMultipleId where A.SubmissionDetailId = T1.Id and B.ForLevel = ?) where T1.SubmissionId = ?", [$level, $id]);
                //SAVE STATUS AS APPROVED/VERIFIED/ETC
                $this->saveStatus($id, $status, Auth::id(), $remarks[0]->Remarks);
                if ($level == 2) {
                    $statusDetails = DB::select("select T1.Id, T1.SubmissionTime, T1.EmployeeId, T2.EmpId,T2.Name as EmployeeName, concat(T2.Name,' of ', T3.Name) as Employee, Z1.Name as GradeStep, T4.Name as Position from pms_submission T1 join (mas_employee T2 join mas_gradestep Z1 on Z1.Id = T2.GradeStepId join mas_department T3 on T3.Id = T2.DepartmentId left join mas_position T4 on T4.Id = T2.PositionId) on T2.Id = T1.EmployeeId where T1.Id = ?", [$id]);
                    $pmsSubmissionId = $statusDetails[0]->Id;
                    $empId = $statusDetails[0]->EmpId;
                    $currentPMSSubmissionDate = $statusDetails[0]->SubmissionTime;
                    DB::update("UPDATE pms_submission T1 set T1.PMSOutcomeId = 1 where T1.Id = ?", [$id]);

                    $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$currentPMSSubmissionDate]);
                    $pmsId = $pmsNumber[0]->Id;

                    $finalScore = $this->getFinalScore($id);

                    DB::delete("DELETE FROM pms_historical where PMSNumberId = ? and EmpId = ?", [$currentPMSId, $empId]);
                    DB::table('pms_submission')->where('Id', $id)->update(['PMSOutcomeId' => CONST_PMSOUTCOME_NOACTION]);
                    DB::insert("INSERT INTO pms_historical (CIDNo,EmpId,PMSNumberId,PMSSubmissionId,PMSSCore,PMSResult,PMSRemarks) SELECT T2.CIDNo,T2.EmpId, ?,?, ?, D.Name, T1.FinalRemarks from pms_submission T1 join mas_employee T2 on T2.Id = T1.EmployeeId left join mas_pmsoutcome D on D.Id = T1.PMSOutcomeId where T1.Id = ?", [$currentPMSId, $pmsSubmissionId, $finalScore, $pmsSubmissionId]);
                }
            }
            //END CALCULATE AVG AND PUSH
            return redirect('appraisepms')->with('successmessage', 'You have appraised PMS for ' . $employee . ' (Emp Id: ' . $empId . ')');
        } else {
            return redirect('processpms/' . $id)->with('successmessage', 'Saved as draft!');
        }
    }

    public function getOfficeOrderHistoryIndex(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $officeOrders = [];
        $periodDetails = [];
        $pmsPeriods = DB::select("select T1.StartDate, T1.PMSNumber from sys_pmsnumber T1 where T1.PMSNumber >= 1 and T1.StartDate < CURDATE() order by PMSNumber DESC");
        foreach ($pmsPeriods as $pmsPeriod):
            $startDate = $pmsPeriod->StartDate;
            $month = date_format(date_create($pmsPeriod->StartDate), 'm');
            $year = date_format(date_create($pmsPeriod->StartDate), 'Y');
            $endDate = date_format(date_create($startDate), 'Y-m-t');
            if ($month == '01') {
                $period = "1st July, " . ($year - 1) . " - 31st December, " . ($year - 1);
            } else {
                $period = "1st Jan, " . $year . " - 30th June, " . $year;
            }
            $periodDetails['Period'] = "PMS " . $pmsPeriod->PMSNumber . "; " . $period;
            $periodDetails['OfficeOrders'] = DB::select("select T1.EmployeeId as Id,T4.Name as Department, T5.Name as Designation, T6.Name as GradeStep, concat(T3.Name,' (Emp Id ',T3.EmpId,')') as Name, T2.Name as Outcome, T1.OfficeOrderPath from viewpmssubmissionwithlaststatus T1 join mas_pmsoutcome T2 on T2.Id = T1.PMSOutcomeId join mas_employee T3 on T3.Id = T1.EmployeeId join mas_department T4 on T4.Id = T1.DepartmentId join mas_designation T5 on T5.Id = T3.DesignationId join mas_gradestep T6 on T6.Id = T3.GradeStepId where T1.PMSOutcomeId is not null and T1.OfficeOrderPath is not null and DATE_FORMAT(T1.SubmissionTime,'%Y-%m-%d') >= ? and DATE_FORMAT(T1.SubmissionTime,'%Y-%m-%d') <= ? order by T4.Name, T3.Name", [$startDate, $endDate]);
            $officeOrders[] = $periodDetails;
            $periodDetails = [];
        endforeach;
        return view('application.officeorderhistory')->with('officeOrders', $officeOrders);
    }

    public function checkAppraiserSubmitted(Request $request): array
    {
        $nextPMSId = $this->getCurrentRound();
        $id = $request->input('id');
        $employeeId = DB::table("pms_submission")->where('Id', $id)->value("EmployeeId");

        $appraiserDetails = $this->getAppraiserDetails($employeeId, false, Auth::id());
        $appraiserLevel = $appraiserDetails['appraiserLevel'];
        $scoreColumn = $appraiserDetails['scoreColumn'];
        if ($appraiserLevel === 1) {
            $goals = DB::table("pms_employeegoal as T1")
                ->join("pms_employeegoaldetail as T2", "T2.EmployeeGoalId", "=", 'T1.Id')
                ->where("T1.EmployeeId", $employeeId)
                ->where("T1.SysPmsNumberId", $nextPMSId)
                ->whereNull("T2.$scoreColumn")
                ->count();
            if ($goals) {
                return ['pass' => false];
            } else {
                return ['pass' => true];
            }
        } else {
            $employeeQuery = DB::table("mas_employee")->where("Id", $employeeId)->get(["PositionId", "DepartmentId"]);
            $employeePositionId = $employeeQuery[0]->PositionId;
            $employeeDepartmentId = $employeeQuery[0]->DepartmentId;
            $level2CriteriaType = DB::table("mas_positiondepartment as T1")
                ->join("mas_positiondepartmentrating as T2", "T2.PositionDepartmentId", "=", "T1.Id")
                ->where("T1.PositionId", $employeePositionId)
                ->where("T1.Departmentid", $employeeDepartmentId)
                ->value("T2.Level2CriteriaType");
            if ((int) $level2CriteriaType === 2) {
                $goals = DB::table("pms_employeegoal as T1")
                    ->join("pms_employeegoaldetail as T2", "T2.EmployeeGoalId", "=", 'T1.Id')
                    ->where("T1.EmployeeId", $employeeId)
                    ->where("T1.SysPmsNumberId", $nextPMSId)
                    ->whereNull("T2.$scoreColumn")
                    ->count();
                if ($goals) {
                    return ['pass' => false, 'level2CriteriaType' => $level2CriteriaType, 'employeeQuery' => $employeeQuery];
                } else {
                    return ['pass' => true];
                }
            } else {
                return ['pass' => true, 'level2CriteriaType' => $level2CriteriaType, 'employeeQuery' => $employeeQuery];
            }
        }
    }

    // remove appraisee scores
    public function removeAppraiseeScoresOfEmployee($submissionId, $laststatusId)
    {
        $employeeDetails = DB::select("SELECT GROUP_CONCAT(A.Name, ' (EmpId: ', A.EmpId, ')') as Employee FROM mas_employee A JOIN pms_submission B ON B.EmployeeId = A.Id WHERE B.Id = ? ", [$submissionId]);
        $employee = $employeeDetails[0]->Employee;

        if ($laststatusId == CONST_PMSSTATUS_DRAFT) {
            PMSSubmission::where("Id", $submissionId)->delete();
            PMSSubmissionDetail::where("SubmissionId", $submissionId)->delete();
            PMSSubmissionHistory::where([["SubmissionId", $submissionId], ["PMSStatusId", CONST_PMSSTATUS_DRAFT]])->delete();
            return back()->with('successmessage', "Draft Scores PMS Status Removed Successfully For $employee.");
        } else if ($laststatusId == CONST_PMSSTATUS_SUBMITTED) {
            PMSSubmission::where("Id", $submissionId)->delete();
            PMSSubmissionDetail::where("SubmissionId", $submissionId)->update(["SelfRating" => NULL]);
            PMSSubmissionHistory::where([["SubmissionId", $submissionId], ["PMSStatusId", CONST_PMSSTATUS_SUBMITTED]])->delete();
            return back()->with('successmessage', "Self Scores PMS Status Removed Successfully For $employee.");
        }
    }

    // remove appraiser scores
    public function removeAppraiserScoresOfEmployee($submissionId, $laststatusId)
    {
        $employeeDetails = DB::select("SELECT GROUP_CONCAT(A.Name, ' (EmpId: ', A.EmpId, ')') as Employee, B.EmployeeId FROM mas_employee A JOIN pms_submission B ON B.EmployeeId = A.Id WHERE B.Id = ? ", [$submissionId]);
        $employee = $employeeDetails[0]->Employee;
        $employeeId = $employeeDetails[0]->EmployeeId;

        $level1AppraiserIdList = [];
        $level2AppraiserIdList = [];

        $getAppraiserLevel = DB::select("SELECT A.ReportingLevel1EmployeeId, A.ReportingLevel2EmployeeId FROM mas_hierarchy A WHERE A.EmployeeId = ? ", [$employeeId]);

        for ($i = 0; $i < count($getAppraiserLevel); $i++) {
            $level1 = $getAppraiserLevel[$i]->ReportingLevel1EmployeeId;
            $level2 = $getAppraiserLevel[$i]->ReportingLevel2EmployeeId;

            if (!empty($level1) && $level1 != null) {
                if (!in_array($level1, $level1AppraiserIdList)) {
                    array_push($level1AppraiserIdList, $level1);
                }
            }
            if (!empty($level2) && $level2 != null) {
                if (!in_array($level2, $level2AppraiserIdList)) {
                    array_push($level2AppraiserIdList, $level2);
                }
            }
        }

        if ($laststatusId == CONST_PMSSTATUS_VERIFIED) {
            if (count($level1AppraiserIdList) > 1) {
                for ($j = 0; $j < count($level1AppraiserIdList); $j++) {
                    $level1AppraiserId = $level1AppraiserIdList[$j];
                    PMSSubmissionMultiple::where([["SubmissionId", $submissionId], ["ForLevel", 1], ["AppraisedByEmployeeId", $level1AppraiserId]])->delete();
                }
            }

            PMSSubmissionDetail::where("SubmissionId", $submissionId)->update(["Level1Rating" => NULL]);
            PMSSubmissionHistory::where([["SubmissionId", $submissionId], ["PMSStatusId", CONST_PMSSTATUS_VERIFIED]])->delete();

            return back()->with('successmessage', "Level 1 Scores PMS Status Removed Successfully For $employee.");
        } else if ($laststatusId == CONST_PMSSTATUS_APPROVED) {
            PMSHistorical::where("PMSSubmissionId", $submissionId)->delete();
            PMSSubmission::where("Id", $submissionId)->update(["PMSOutcomeId" => NULL]);
            PMSSubmissionHistory::where([["SubmissionId", $submissionId], ["PMSStatusId", CONST_PMSSTATUS_APPROVED]])->delete();

            if (!empty($level1AppraiserIdList) && empty($level2AppraiserIdList)) {
                if (count($level1AppraiserIdList) > 1) {
                    for ($j = 0; $j < count($level1AppraiserIdList); $j++) {
                        $level1AppraiserId = $level1AppraiserIdList[$j];
                        PMSSubmissionMultiple::where([["SubmissionId", $submissionId], ["ForLevel", 1], ["AppraisedByEmployeeId", $level1AppraiserId]])->delete();
                    }
                }

                PMSSubmissionDetail::where("SubmissionId", $submissionId)->update(["Level1Rating" => NULL]);

                return back()->with('successmessage', "Level 1 Scores PMS Status Removed Successfully For $employee.");
            } else if (!empty($level2AppraiserIdList)) {
                if (count($level2AppraiserIdList) > 1) {
                    for ($j = 0; $j < count($level2AppraiserIdList); $j++) {
                        $level2AppraiserId = $level2AppraiserIdList[$j];
                        PMSSubmissionMultiple::where([["SubmissionId", $submissionId], ["ForLevel", 2], ["AppraisedByEmployeeId", $level2AppraiserId]])->delete();
                    }
                }

                PMSSubmissionDetail::where("SubmissionId", $submissionId)->update(["Level2Rating" => NULL]);

                return back()->with('successmessage', "Level 2 Scores PMS Status Removed Successfully For $employee.");
            }
        }
    }
    public function updateScore(Request $request)
    {
        $assessment = PMSSubmissionDetail::find($request->id);


        if ($assessment) {
            if ($request->type == "self") {
                $assessment->SelfRating = $request->value;
            } elseif ($request->type == "Level1Rating") {
                $assessment->Level1Rating = $request->value;
            } elseif ($request->type == "Level2Rating") {
                $assessment->Level2Rating = $request->value;
            }

            $assessment->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function updateFinalScore(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pms_historical,pmssubmissionid',
            'finalScore' => 'required|numeric|min:0'
        ]);

        try {
            $finalScore = $request->finalScore;

            $updated = DB::table('pms_historical')->where('pmssubmissionid', $request->id)->update(['PMSScore' => $finalScore]);

            if ($updated) {
                return response()->json(['success' => true, 'message' => 'Final score updated successfully.']);
            }

            return response()->json(['success' => false, 'message' => 'No records updated. Submission may not exist.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating score.', 'error' => $e->getMessage()], 500);
        }
    }
}
