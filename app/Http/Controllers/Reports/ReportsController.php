<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Reports;

use App\Models\PositionDepartment;
use App\Models\PositionDepartmentRating;
use App\Models\PositionDepartmentRatingCriteria;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
use App\Models\Position;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once '../phpspreadsheet/vendor/autoload.php';

class ReportsController extends Controller
{
    protected $appraiserCheck;

    function checkAppraiser()
    {
        if (!in_array(Auth::user()->RoleId, [1])) {
            $this->appraiserCheck = true;
        } else {
            $this->appraiserCheck = false;
        }
    }

    public function getPMSComparisionEmployees(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->checkAppraiser();
        $pmsPeriodArray = [];
        $pmsResultData = [];
        $parameters = [];
        $employees = [];
        $hasParams = false;
        $pmsPeriods = $this->pmsPeriodsForReports();
        $appointmentDates = DB::select("select count(distinct T2.EmpId) as Number,T1.DateOfAppointment from mas_employee T1 join pms_historical T2 on T2.EmpId = T1.EmpId where T1.DateOfAppointment is not null group by T1.DateOfAppointment");
        $designations = DB::table('mas_designation')->orderBy("Name")->get(array('Id', 'Name'));
        $gradeSteps = DB::table('mas_gradestep')->orderBy("Name")->get(['Id', 'Name']);

        if ($this->appraiserCheck) {
            $employeeList = $this->getAllEmployees(" and T1.DepartmentId = ?", [Auth::user()->DepartmentId]);
        } else {
            $employeeList = $this->getAllEmployees();
        }

        $appointmentDate = $request->input('AppointmentDate');
        $designation = $request->input('Designation');
        $gradeStep = $request->input('GradeStep');
        $pmsPeriod = $request->input('PMSPeriod');
        $employeeId = $request->input('EmployeeId');

        $condition = " 1=1";
        if ($appointmentDate) {
            $condition .= " and T2.DateOfAppointment = ?";
            $parameters[] = $appointmentDate;
            $hasParams = true;
        }
        if ($designation) {
            $condition .= " and T2.DesignationId = ?";
            $parameters[] = $designation;
            $hasParams = true;
        }
        if ($gradeStep) {
            $condition .= " and T2.GradeStepId = ?";
            $parameters[] = $gradeStep;
            $hasParams = true;
        }
        if (!empty($employeeId)) {
            $hasParams = true;
            $conditionAppend = "";
            $empConditionCount = 0;
            foreach ($employeeId as $singleEmployeeId):
                if ($singleEmployeeId) {
                    $empConditionCount += 1;
                    if ($conditionAppend != "") {
                        $conditionAppend .= ",";
                    }
                    $conditionAppend .= "?";
                    $parameters[] = $singleEmployeeId;
                }
            endforeach;
            if ($empConditionCount > 0) {
                $conditionAppend = " and T2.Id in ($conditionAppend)";
                $condition .= $conditionAppend;
            }
        }

        if (!empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }
        $pmsPeriodString = (implode(",", $pmsPeriodArray));
        if ($hasParams && $pmsPeriodString) {
            if ($this->appraiserCheck) {
                $parameters[] = Auth::user()->DepartmentId;
                $employees = DB::select("select distinct T2.Id, T2.Name, T2.EmpId, T2.BasicPay as 'Basic Pay', T4.Name as Dept, T2.CIDNo, T2.DateOfAppointment as DoA, T2.DateOfAppointment as DateOfAppointmentRaw, T2.DateOfAppointment as DateOfRegularization, T3.Name as Designation, T2.JobLocation as 'Work Location', A.Name as Grade from pms_historical T1 join mas_employee T2 on TRIM(T2.EmpId) = TRIM(T1.EmpId) join mas_gradestep A on A.Id = T2.GradeStepId join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T2.DepartmentId left join mas_section T5 on T5.Id = T2.SectionId where T1.PMSNumberId in (" . $pmsPeriodString . ") and$condition and T2.DepartmentId = ?", $parameters);
            } else {
                $employees = DB::select("select distinct T2.Id, T2.Name, T2.EmpId, T2.BasicPay as 'Basic Pay', T4.Name as Dept, T2.CIDNo, T2.DateOfAppointment as DoA, T2.DateOfAppointment as DateOfAppointmentRaw, T2.DateOfAppointment as DateOfRegularization, T3.Name as Designation, T2.JobLocation as 'Work Location', A.Name as Grade from pms_historical T1 join mas_employee T2 on TRIM(T2.EmpId) = TRIM(T1.EmpId) join mas_gradestep A on A.Id = T2.GradeStepId join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T2.DepartmentId left join mas_section T5 on T5.Id = T2.SectionId where T1.PMSNumberId in (" . $pmsPeriodString . ") and$condition", $parameters);
            }

            foreach ($employees as $employee):
                foreach ($pmsPeriodArray as $pmsPeriodId):
                    $pmsResultData[$employee->Id][$pmsPeriodId] = DB::select("select T1.PMSResult,T1.PMSScore,T1.PMSRemarks from pms_historical T1 where T1.PMSNumberId = ? and T1.EmpId = ?", [$pmsPeriodId, $employee->EmpId]);
                endforeach;
            endforeach;
        }
        return view('reports.pmscomparisionemployees', ['employeeList' => $employeeList, 'employees' => $employees, 'pmsPeriodArray' => $pmsPeriodArray, 'pmsResultData' => $pmsResultData, 'appointmentDates' => $appointmentDates, 'designations' => $designations, 'gradeSteps' => $gradeSteps, 'pmsPeriods' => $pmsPeriods]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function getPMSScoreReport(Request $request)
    {
        $this->checkAppraiser();
        $parameters = [];
        $condition = " 1=1";
        if ($this->appraiserCheck) {
            $departments = $this->fetchActiveDepartments("and Id = ?", [Auth::user()->DepartmentId]);
            $sections = $this->fetchSections("and Id = ?", [Auth::user()->SectionId]);
        } else {
            $departments = $this->fetchActiveDepartments();
            $sections = $this->fetchSections();
        }

        $pmsPeriods = DB::select("select T1.Id, T1.PMSNumber, T1.StartDate from sys_pmsnumber T1 where T1.PMSNumber > 0 and T1.Status = 2 union
select T1.Id, T1.PMSNumber, T1.StartDate from sys_pmsnumber T1 where T1.StartDate < CURDATE() and T1.Status <> 2 order by PMSNumber");
        $pmsYears = DB::select("select distinct YEAR(T1.StartDate) as Year from sys_pmsnumber T1 where T1.PMSNumber > 0 and T1.Status = 2 union all
select distinct YEAR(T1.StartDate) as Year from sys_pmsnumber T1 where T1.StartDate < CURDATE() and T1.Status <> 2 order by Year");
        $outcomes = DB::select("select * from mas_pmsoutcome order by Id");
        $pmsPeriodArray = [];
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('StartDate');
        $pmsStartDate = $currentPMSQuery[0];
        $departmentId = $request->input('DepartmentId');
        $employeeIds = $request->input('EmployeeId');
        $sectionId = $request->input('SectionId');
        $pmsPeriod = $request->input('PMSPeriod');
        $fromYear = $request->input("FromYear");
        $toYear = $request->input("ToYear");
        $parameters[] = $pmsStartDate;
        if ($fromYear) {
            if (!$toYear) {
                $toYear = $pmsYears[count($pmsYears) - 1]->Year;
            }

            // Status = 2 and
            $pmsPeriodsFromYears = DB::select("select Id from sys_pmsnumber where YEAR(StartDate) >= ? and YEAR(StartDate) <= ?", [$fromYear, $toYear]);
            foreach ($pmsPeriodsFromYears as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        } else {
            if (!empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
                $pmsPeriodArray = $pmsPeriod;
            } else {
                foreach ($pmsPeriods as $pmsPeriod):
                    $pmsPeriodArray[] = $pmsPeriod->Id;
                endforeach;
            }
        }

        if ($sectionId) {
            $employees = $this->getSectionEmployees($sectionId, false, false);
        } else {
            if ($departmentId) {
                $employees = $this->getDepartmentEmployees($departmentId, false, false);
            }
        }

        //FETCH REPORT DATA
        if ($departmentId) {
            $queryAppend = '';
            foreach ($pmsPeriodArray as $pmsPeriodId):
                if ($queryAppend != '') {
                    $queryAppend .= ",";
                }
                $queryAppend .= " coalesce((select coalesce(A.PMSResult,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Result'";
                $queryAppend .= ", coalesce((select coalesce(A.PMSScore,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
                $queryAppend .= ", coalesce((select coalesce(A.PMSRemarks,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Remarks'";
                $queryAppend .= ", (select A.Id from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?) as '$pmsPeriodId Id'";
                $queryAppend .= ", (select A.PMSSubmissionId from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?) as '$pmsPeriodId SubmissionId'";
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
            endforeach;

            if ($employeeIds) {
                $employeeCondition = "";
                foreach ($employeeIds as $employeeId):
                    if ($employeeCondition == '') {
                        $employeeCondition .= " and T2.Id in (";
                    } else {
                        $employeeCondition .= ",";
                    }
                    $employeeCondition .= "?";
                    $parameters[] = $employeeId;
                endforeach;
                if ($employeeCondition != "") {
                    $employeeCondition .= ")";
                    $condition .= $employeeCondition;
                }
            } else {
                if ($sectionId) {
                    $employees = $this->getSectionEmployees($sectionId, false, false);
                    $condition .= " and T2.SectionId = ?";
                    $parameters[] = $sectionId;
                } else {
                    $employees = $this->getDepartmentEmployees($departmentId, false, false);
                    $condition .= " and T2.DepartmentId = ?";
                    $parameters[] = $departmentId;
                }
            }

            if ($queryAppend == '') {
                $queryAppend .= " 'zz' as test ";
            }
            $query = "SELECT distinct T2.Id, T2.Name as Employee,T2.DateOfAppointment as DateOfAppointmentRaw, T2.DateOfAppointment as DateOfRegularization,  C.Name as GradeStep, T2.EmpId, (select pp.SavedPMSOutcomeId from viewpmssubmissionwithlaststatus pp where pp.EmployeeId = T2.Id and pp.SubmissionTime >= ? order by pp.SubmissionTime DESC limit 1) as SavedPMSOutcomeId, T3.Name as Designation, T2.JobLocation, DATE_FORMAT(T2.DateOfAppointment,'%D %b, %Y') as DateOfAppointment, T2.BasicPay, T2.CIDNo, T4.Name as Department, T5.Name as Section,$queryAppend from mas_employee T2 join mas_gradestep C on C.Id = T2.GradeStepId join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T2.DepartmentId left join mas_section T5 on T5.Id = T2.SectionId where T2.EmpId in (select distinct EmpId from pms_historical) and $condition order by T4.Name, T5.Name, T2.Name";
            $result = DB::select("$query", $parameters);
        } else {
            $result = [];
        }
        //END FETCH REPORT DATA

        if (!empty($result)) {
            if ($request->has('export') && $request->input('export') == 'excel') {
                $spreadsheet = new Spreadsheet();
                $spreadsheet->getProperties()->setCreator('PMS')
                    ->setLastModifiedBy('PMS')
                    ->setTitle('PMS Score Report _ ' . date("Y_m_d_H_i_s"))
                    ->setSubject('PMS Score Report')
                    ->setDescription('PMS Score Report')
                    ->setKeywords('PMS')
                    ->setCategory('Report');

                $cellArrays = [
                    "K1",
                    "L1",
                    "M1",
                    "N1",
                    "O1",
                    "P1",
                    "Q1",
                    "R1",
                    "S1",
                    "T1",
                    "U1",
                    "V1",
                    "W1",
                    "X1",
                    "Y1",
                    "Z1",
                    "AA1",
                    "AB1",
                    "AC1",
                    "AD1",
                    "AE1",
                    "AF1",
                    "AG1",
                    "AH1",
                    "AI1",
                    "AJ1",
                    "AK1",
                    "AL1",
                    "AM1",
                    "AN1",
                    "AO1",
                    "AP1",
                    "AQ1",
                    "AR1",
                    "AS1",
                    "AT1",
                    "AU1",
                    "AV1",
                    "AW1",
                    "AX1",
                    "AY1",
                    'AZ1',
                    "BA1",
                    "BB1",
                    "BC1",
                    "BD1",
                    "BE1",
                    "BF1",
                    "BG1",
                    "BH1",
                    "BI1",
                    "BJ1",
                    "BK1",
                    "BL1",
                    "BM1",
                    "BN1",
                    "BO1",
                    "BP1",
                    "BQ1",
                    "BR1",
                    "BS1",
                    "BT1",
                    "BU1",
                    "BV1",
                    "BW1",
                    "BX1",
                    "BY1",
                    'BZ1'
                ];
                $columnArrays = [
                    "A",
                    "B",
                    "C",
                    "D",
                    "E",
                    "F",
                    "G",
                    "H",
                    "I",
                    "J",
                    "K",
                    "L",
                    "M",
                    "N",
                    "O",
                    "P",
                    "Q",
                    "R",
                    "S",
                    "T",
                    "U",
                    "V",
                    "W",
                    "X",
                    "Y",
                    "Z",
                    "AA",
                    "AB",
                    "AC",
                    "AD",
                    "AE",
                    "AF",
                    "AG",
                    "AH",
                    "AI",
                    "AJ",
                    "AK",
                    "AL",
                    "AM",
                    "AN",
                    "AO",
                    "AP",
                    "AQ",
                    "AR",
                    "AS",
                    "AT",
                    "AU",
                    "AV",
                    "AW",
                    "AX",
                    "AY",
                    'AZ',
                    "BA",
                    "BB",
                    "BC",
                    "BD",
                    "BE",
                    "BF",
                    "BG",
                    "BH",
                    "BI",
                    "BJ",
                    "BK",
                    "BL",
                    "BM",
                    "BN",
                    "BO",
                    "BP",
                    "BQ",
                    "BR",
                    "BS",
                    "BT",
                    "BU",
                    "BV",
                    "BW",
                    "BX",
                    "BY",
                    'BZ'
                ];
                // Create a first sheet
                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->setTitle('Report')->setCellValue('A1', 'Employee')
                    ->setCellValue('B1', 'DoA')
                    ->setCellValue('C1', 'Duration of Service')
                    ->setCellValue('D1', 'Basic Pay')
                    ->setCellValue('E1', 'CID')
                    ->setCellValue('F1', 'Dept.')
                    ->setCellValue('G1', 'Designation')
                    ->setCellValue('H1', 'Work Location')
                    ->setCellValue('I1', 'Grade')
                    ->setCellValue('J1', 'Section');
                $cellArrayIndex = 0;
                foreach ($pmsPeriods as $pmsPeriod):
                    if (in_array($pmsPeriod->Id, $pmsPeriodArray)):
                        $spreadsheet->getActiveSheet()->setCellValue($cellArrays[$cellArrayIndex++], date_format(date_create($pmsPeriod->StartDate), 'M, Y'));
                        $spreadsheet->getActiveSheet()->setCellValue($cellArrays[$cellArrayIndex++], "Result");
                        $spreadsheet->getActiveSheet()->setCellValue($cellArrays[$cellArrayIndex++], "Remarks");
                    endif;
                endforeach;

                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
                $spreadsheet->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);

                $cellArrayIndex = 10;
                foreach ($pmsPeriods as $pmsPeriod):
                    if (in_array($pmsPeriod->Id, $pmsPeriodArray)):
                        $spreadsheet->getActiveSheet()->getColumnDimension($columnArrays[$cellArrayIndex++])->setAutoSize(true);
                        $spreadsheet->getActiveSheet()->getColumnDimension($columnArrays[$cellArrayIndex++])->setAutoSize(true);
                        $spreadsheet->getActiveSheet()->getColumnDimension($columnArrays[$cellArrayIndex++])->setAutoSize(true);
                    endif;
                endforeach;

                $index = 2;
                foreach ($result as $singleResult):
                    $cellArrayIndex = 0;
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->Employee . " (" . $singleResult->EmpId . ")");
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->DateOfAppointmentRaw);

                    $dateOfRegularization = date_create($singleResult->DateOfRegularization);
                    $now = date_create(date("Y-m-d"));
                    $diff = $dateOfRegularization->diff($now);
                    $diffYears = $diff->format("%y");
                    $diffMonths = $diff->format("%m");
                    if ($now > $dateOfRegularization):
                        if ($diffYears > 0):
                            if ($diffMonths == 0):
                                $durationOfService = $diff->format("%y Years");
                            else:
                                $durationOfService = $diff->format("%y Years and %m Months");
                            endif;
                        else:
                            if ($diffMonths > 0):
                                $durationOfService = $diff->format("%m Months");
                            else:
                                $durationOfService = "N/A";
                            endif;
                        endif;
                    else:
                        $durationOfService = "N/A";
                    endif;
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $durationOfService);
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->BasicPay);
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->CIDNo);
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->Department);
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->Designation);
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->JobLocation);
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->GradeStep);
                    $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->Section ?: '');
                    foreach ($pmsPeriods as $pmsPeriod):
                        if (in_array($pmsPeriod->Id, $pmsPeriodArray)):
                            $scoreVar = $pmsPeriod->Id . " Score";
                            $resultVar = $pmsPeriod->Id . " Result";
                            $remarksVar = $pmsPeriod->Id . " Remarks";
                            $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->$scoreVar);
                            $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->$resultVar);
                            $spreadsheet->getActiveSheet()->setCellValue($columnArrays[$cellArrayIndex++] . "$index", $singleResult->$remarksVar);
                        endif;
                    endforeach;
                    $index++;
                endforeach;
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

                $writer->save("PMS_Score_Report _ " . date("Y_m_d_H_i_s") . ".xlsx");
                return response()->download("PMS_Score_Report _ " . date("Y_m_d_H_i_s") . ".xlsx");
            }
        }

        return view('reports.pmsscorereport', ['pmsYears' => $pmsYears, 'outcomes' => $outcomes, 'result' => $result, 'employees' => isset($employees) ? $employees : [], 'pmsPeriodArray' => $pmsPeriodArray, 'pmsPeriods' => $pmsPeriods, 'departments' => $departments, 'sections' => $sections]);
    }

    public function getPMSScoreReportData(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->checkAppraiser();
        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];
        $employees = [];
        $condition = " 1=1";

        $departmentId = $request->input('DepartmentId');
        $employeeId = $request->input('EmployeeId');
        $sectionId = $request->input('SectionId');
        $pmsPeriod = $request->input('PMSPeriod');

        if (!empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= " coalesce((select coalesce(A.PMSResult,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Result'";
            $queryAppend .= ", coalesce((select coalesce(A.PMSScore,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $queryAppend .= ", coalesce((select coalesce(A.PMSRemarks,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Remarks'";
            $parameters[] = $pmsPeriodId;
            $parameters[] = $pmsPeriodId;
            $parameters[] = $pmsPeriodId;
        endforeach;

        if ($employeeId) {
            $condition .= " and T2.Id = ?";
            $parameters[] = $employeeId;
            $hasParams = true;
        } else {
            if ($sectionId) {
                $employees = $this->getSectionEmployees($sectionId, false, false);
                $condition .= " and T2.SectionId = ?";
                $parameters[] = $sectionId;
                $hasParams = true;
            } else {
                if ($departmentId) {
                    $employees = $this->getDepartmentEmployees($departmentId, false, false);
                    $condition .= " and T2.DepartmentId = ?";
                    $parameters[] = $departmentId;
                    $hasParams = true;
                }
            }
        }

        $query = "select distinct T2.Id, T2.Name as Employee,T2.DateOfAppointment as DateOfAppointmentRaw, T2.DateOfAppointment as DateOfRegularization,  C.Name as GradeStep, T2.EmpId, T3.Name as Designation, T2.JobLocation, DATE_FORMAT(T2.DateOfAppointment,'%D %b, %Y') as DateOfAppointment, T2.BasicPay, T2.CIDNo, T4.Name as Department, T5.Name as Section,$queryAppend from mas_employee T2 join mas_gradestep C on C.Id = T2.GradeStepId join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T2.DepartmentId left join mas_section T5 on T5.Id = T2.SectionId where T2.EmpId in (select distinct EmpId from pms_historical) and $condition order by T4.Name, T5.Name";
        $result = DB::select("$query", $parameters);
        return response()->json($result);
    }

    public function getSectionWisePerformance(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->checkAppraiser();
        if ($this->appraiserCheck) {
            $departments = $this->fetchActiveDepartments("and Id = ?", [Auth::user()->DepartmentId]);
        } else {
            $departments = $this->fetchActiveDepartments();
        }
        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];
        $condition = " 1=1";

        if ($this->appraiserCheck) {
            $departmentId = Auth::user()->DepartmentId;
        } else {
            $departmentId = $request->input('DepartmentId');
        }

        $pmsPeriod = $request->input('PMSPeriod');

        if (!empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= "coalesce((select AVG(coalesce(A.PMSScore,'')) from pms_historical A join mas_employee B on B.EmpId = A.EmpId where A.PMSScore > 0 and B.SectionId = T1.Id and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $parameters[] = $pmsPeriodId;
        endforeach;

        if ($departmentId) {
            $condition .= " and T1.DepartmentId = ?";
            $parameters[] = $departmentId;
        }

        $query = "select distinct T1.Id, T1.Name as Section, T2.Name as Department, $queryAppend from mas_section T1 join mas_department T2 on T2.Id = T1.DepartmentId where $condition order by T2.Name,T1.Name";

        $result = DB::select("$query", $parameters);
        return view('reports.pmsscoresection')->with('pmsPeriodArray', $pmsPeriodArray)->with('pmsPeriods', $pmsPeriods)->with('result', $result)->with('departments', $departments);
    }

    public function getDepartmentWisePerformance(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->checkAppraiser();
        if ($this->appraiserCheck) {
            $departments = $this->fetchActiveDepartments("and Id = ?", [Auth::user()->DepartmentId]);
        } else {
            $departments = $this->fetchActiveDepartments();
        }

        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];
        $condition = " 1=1";

        $pmsPeriod = $request->input('PMSPeriod');
        $departmentId = $request->input('DepartmentId');

        if (!empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= "coalesce((select AVG(coalesce(A.PMSScore,'')) from pms_historical A join mas_employee B on B.EmpId = A.EmpId where A.PMSScore > 0 and B.DepartmentId = T1.Id and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $parameters[] = $pmsPeriodId;
        endforeach;

        if ($departmentId) {
            $condition .= " and T1.Id = ?";
            $parameters[] = $departmentId;
        }

        if ($this->appraiserCheck) {
            $parameters[] = Auth::user()->DepartmentId;
            $query = "select T1.Name as Department, $queryAppend from mas_department T1 where $condition and T1.Id = ? order by T1.Name";
        } else {
            $query = "select T1.Name as Department, $queryAppend from mas_department T1 where $condition order by T1.Name";
        }

        $result = DB::select("$query", $parameters);
        return view('reports.pmsscoredepartment')->with('departments', $departments)->with('pmsPeriodArray', $pmsPeriodArray)->with('pmsPeriods', $pmsPeriods)->with('result', $result);
    }

    public function getOrganizationalPerformance(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->checkAppraiser();
        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];

        $pmsPeriod = $request->input('PMSPeriod');

        if (!empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= "coalesce((select AVG(coalesce(A.PMSScore,'')) from pms_historical A join mas_employee B on B.EmpId = A.EmpId where A.PMSScore > 0 and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $parameters[] = $pmsPeriodId;
        endforeach;
        $companyName = \config("app.name");
        $companyName = str_replace(" Online PMS", "", $companyName);
        $query = "select '$companyName' as Organization, $queryAppend";

        $result = DB::select("$query", $parameters);
        return view('reports.pmsscoreorganization')->with('pmsPeriodArray', $pmsPeriodArray)->with('pmsPeriods', $pmsPeriods)->with('result', $result);
    }

    public function getAuditTrailReport(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->checkAppraiser();
        $perPage = 8;

        $userId = $request->input('UserId');
        $tableName = $request->input('TableName');
        $deleted = $request->input('Deleted');
        $fromDate = $request->input('FromDate');
        $toDate = $request->input('ToDate');

        $append = '1=1';
        $parameters = [];
        if ($userId) {
            $append .= " and T1.EmployeeId = ?";
            $parameters[] = $userId;
        }
        if ($tableName) {
            $append .= " and SUBSTR(T1.TableName,5) = ?";
            $parameters[] = $tableName;
        }
        if ($fromDate) {
            $append .= " and T1.ChangedOn >= ?";
            $parameters[] = "$fromDate 00:00:00";
        }
        if ($toDate) {
            $append .= " and T1.ChangedOn <= ?";
            $parameters[] = "$toDate 23:59:59";
        }
        if ($deleted === '0' || $deleted === '1') {
            $append .= " and T1.Deleted = ?";
            $parameters[] = $deleted;
        }

        $adminUsers = DB::select("select distinct T2.Id, T2.Name from sys_databasechangehistory T1 join mas_employee T2 on T2.Id = T1.EmployeeId");
        $tables = DB::select("select distinct TableName from sys_databasechangehistory");
        $reportData = DB::table('sys_databasechangehistory as T1')
            ->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')
            ->whereRaw("$append", $parameters)
            ->select('T2.Name', 'T1.TableName', DB::raw("DATE_FORMAT(T1.ChangedOn,'%D %b, %Y %l:%i %p') as ChangedOn"), DB::raw("case when T1.Deleted = 1 then 'Yes' else 'No' end as Deleted"), 'T1.Changes')
            ->orderBy("T1.ChangedOn", "DESC")
            ->paginate($perPage);
        return view('reports.audittrail')->with('adminUsers', $adminUsers)->with('tables', $tables)->with('perPage', $perPage)->with('reportData', $reportData);
    }

    public function getEligibleForIncentive(Request $request)
    {
        $this->checkAppraiser();
        $queryCondition = "";
        $queryParam = [];
        if ($request->has("DepartmentId")) {
            $queryCondition .= " and T1.DepartmentId = ?";
            $queryParam[] = $request->input("DepartmentId");
        }
        $outstandingEligible = DB::select("select T1.Id,T1.EmpId, T2.OutstandingCount as Requirement,(select max(A.PMSNumber) from view_pmshistorical A
where A.EmpId = T1.EmpId and TRIM(A.PMSResult) in ('SP','DP')) AS `Round of Last Reward`, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep,
T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section, (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore >= 92
and A.PMSNumber > COALESCE(`Round of Last Reward`,1) ) as `Achieved` from mas_employee T1 JOIN
pms_promotioncriteria T2 on T1.GradeStepId = T2.FromGradeStepId join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN
mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 $queryCondition having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);

        $append = "1=1";
        $outstandingEligibleIds = [];
        foreach ($outstandingEligible as $outstandingSingle):
            $append .= " and T1.Id <> ?";
            $queryParam[] = $outstandingSingle->Id;
            $outstandingEligibleIds[] = $outstandingSingle->Id;
        endforeach;

        $outstandingAndGoodEligible = DB::select("select T1.Id,T1.EmpId, T2.OutstandingAndGoodCount as Requirement,(select max(A.PMSNumber) from view_pmshistorical A
where A.EmpId = T1.EmpId and TRIM(A.PMSResult) in ('SP','DP')) AS `Round of Last Reward`, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep,
T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section, (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore >= 80
and A.PMSNumber > COALESCE(`Round of Last Reward`,1) ) as `Achieved` from mas_employee T1 JOIN
pms_promotioncriteria T2 on T1.GradeStepId = T2.FromGradeStepId join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN
mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 and $append$queryCondition having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);

        $outstandingAndGoodEligibleIds = [];
        foreach ($outstandingAndGoodEligible as $outstandingAndGoodSingle):
            $append .= " and T1.Id <> ?";
            $queryParam[] = $outstandingAndGoodSingle->Id;
            $outstandingAndGoodEligibleIds[] = $outstandingAndGoodSingle->Id;
        endforeach;

        $currentRound = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->value('PMSNumber');
        $threeRoundsIncludingCurrent = DB::table("sys_pmsnumber")->whereRaw("PMSNumber <= ?", [$currentRound])->orderBy("PMSNumber", "DESC")->take(3)->pluck("PMSNumber")->toArray();
        $threeRoundsIncludingCurrentString = implode(",", $threeRoundsIncludingCurrent);

        $loaEligible = DB::select("select T1.Id,T1.EmpId, 3 as Requirement,(select max(A.PMSNumber) from view_pmshistorical A
where A.EmpId = T1.EmpId and TRIM(coalesce(A.PMSResult,'No Action')) in ('SP','DP','DI','LoA','SP + SH','SP + SH (Manager)','Technical Supervisor','SP + SI','Section Head','SI + SH','SP & Section Head','SH','LoI','SI','PP','PP + SI','PP to TS','Letter by AND','LLW','DP and Change in Designation','LAI','LAI + Mentoring')) AS `Round of Last Action`, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep,
T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section, (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore >= 92
and A.PMSNumber > COALESCE(`Round of Last Action`,1) and A.PMSNumber in ($threeRoundsIncludingCurrentString) ) as `Achieved` from mas_employee T1 join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN
mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 and $append$queryCondition having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);
        return view("reports.eligibleforincentives")
            ->with('departments', $this->fetchActiveDepartments())
            ->with('outstandingAndGoodEligible', $outstandingAndGoodEligible)
            ->with('outstandingEligible', $outstandingEligible)
            ->with('loaEligible', $loaEligible)
            ->with('outstandingEligibleIds', $outstandingEligibleIds)
            ->with('outstandingAndGoodEligibleIds', $outstandingAndGoodEligibleIds);
    }

}
