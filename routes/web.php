<?php /** @noinspection ALL */

use App\Http\Controllers\Application\DashboardController;
use App\Http\Controllers\Application\DepartmentController;
use App\Http\Controllers\Application\DesignationController;
use App\Http\Controllers\Application\DisciplinaryController;
use App\Http\Controllers\Application\EmpDetailsController;
use App\Http\Controllers\Application\EmployeeController;
use App\Http\Controllers\Application\GoalController;
use App\Http\Controllers\Application\GradeStepController;
use App\Http\Controllers\Application\HierarchyController;
use App\Http\Controllers\Application\PMSController;
use App\Http\Controllers\Application\PositionController;
use App\Http\Controllers\Application\ProfileController;
use App\Http\Controllers\Application\SectionController;
use App\Http\Controllers\Files\FileController;
use App\Http\Controllers\Reports\ReportsController;
use App\Http\Controllers\SystemAdmin\BugController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Application\AuthController;
use App\Http\Controllers\Application\SupervisorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('testpendingunsubmitted', 'Application\PMSController@checkAllPMSCompleted');
Route::get('clear-cache/{var1}/{var2}', function ($var1, $var2) {
    if ($var1 == '666' && $var2 == 'NotB') {
        $exitCode = Artisan::call('config:clear');
        $exitCode = Artisan::call('cache:clear');
        $exitCode = Artisan::call('config:cache');
        $exitCode = Artisan::call('view:clear');
        return 'DONE'; //Return anything
    }
});
//END
Route::get('insertscores', [Controller::class, 'insertScores']);
Route::get('assigncriteria', [Controller::class, 'assignPositionId']);
Route::get('officesuitedashboard', 'Controller@officeSuiteDashboard');
Route::get('/', [AuthController::class, 'getLogin'])->name("login");
Route::get('testsms', [Controller::class, 'testSMS'])->name("testsms");
Route::get('updatesalary', [GradeStepController::class, 'updateSalaryDetailsForGradeStep']);
Route::get('logout', [AuthController::class, "getLogout"])->name("logout");
Route::get('forgotpassword', [AuthController::class, 'forgotPassword']);
Route::get('_sign_', [AuthController::class, 'getSigninFromExternal']);
Route::middleware(['guest'])->group(function () {
    Route::group(['before' => 'csrf'], function () {
        Route::post('auth', [AuthController::class, 'postAuth']);
    });
});

Route::get('bDay_wishes', ['uses' => 'Application\BirthdayWishController@getIndex']);
Route::middleware(['auth', 'isadmin'])->group(function () {
    Route::get('departmentindex', [DepartmentController::class, 'getIndex']);
    Route::get('departmentinput/{id?}', [DepartmentController::class, 'getForm']);
    Route::post('savedepartment', [DepartmentController::class, 'postSave']);
    Route::get('departmentdelete/{id}', [DepartmentController::class, 'getDelete']);

    Route::get('sectionindex', [SectionController::class, 'getIndex']);
    Route::get('sectioninput/{id?}', [SectionController::class, 'getForm']);
    Route::post('savesection', [SectionController::class, 'postSave']);
    Route::get('sectiondelete/{id}', [SectionController::class, 'getDelete']);

    Route::get('gradestepindex', [GradeStepController::class, 'getIndex']);
    Route::get('gradestepinput/{id?}', [GradeStepController::class, 'getForm']);
    Route::post('savegradestep', [GradeStepController::class, 'postSave']);
    Route::get('gradestepdelete/{id}', [GradeStepController::class, 'getDelete']);

    Route::get('designationindex', [DesignationController::class, 'getIndex']);
    Route::get('designationinput/{id?}', [DesignationController::class, 'getForm']);
    Route::post('savedesignation', [DesignationController::class, 'postSave']);
    Route::get('designationdelete/{id}', [DesignationController::class, 'getDelete']);

    Route::get('hierarchyindex', [HierarchyController::class, 'getIndex']);
    Route::get('hierarchyinput/{id}', [HierarchyController::class, 'getForm']);
    Route::post('savehierarchy', [HierarchyController::class, 'postSave']);
    Route::get('hierarchydelete/{id}', [HierarchyController::class, 'getDelete']);

    Route::get('employeeindex', [EmployeeController::class, 'getIndex']);
    Route::get('employeeinput/{id?}', [EmployeeController::class, 'getForm']);
    Route::post('saveemployee', [EmployeeController::class, 'postSave']);
    Route::get('employeedelete/{id}', [EmployeeController::class, 'getDelete']);

    Route::get('supervisorindex', [SupervisorController::class, 'getIndex']);
    Route::get('supervisorinput/{id?}', [SupervisorController::class, 'getForm']);
    Route::post('savesupervisor', [SupervisorController::class, 'postSave']);
    Route::get('supervisordelete/{id}', [SupervisorController::class, 'getDelete']);

    Route::get('positionindex', [PositionController::class, 'getIndex']);
    Route::get('positioninput/{id?}', [PositionController::class, 'getForm']);
    Route::post('saveposition', [PositionController::class, 'postSave']);
    Route::get('positiondelete/{id}', [PositionController::class, 'getDelete']);

    Route::get("criteriainput/{deptId}/{positionId}", [PositionController::class, 'fetchForm']);
    Route::post('savecriteria', [PositionController::class, 'saveCriteria']);

    Route::get("resetpassword", [EmployeeController::class, 'postResetPassword']);

    Route::get('disciplinaryinput/{id?}', [DisciplinaryController::class, 'getForm']);
    Route::post('savedisciplinary', [DisciplinaryController::class, 'saveDisciplinary']);
    Route::get('disciplinarydelete/{id}', [DisciplinaryController::class, 'getDelete']);

    Route::get('openpms', [PMSController::class, 'getOpenPMS']);
    Route::get('openpmsprocess', [PMSController::class, 'openPMS']);
    Route::get('closepms', [PMSController::class, 'getClose']);
    Route::get('closepmsprocess', [PMSController::class, 'postClose']);
});
Route::middleware(['auth'])->group(function () {
    Route::get('checklogs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('index', [DashboardController::class, 'getIndex'])->name('index');
    Route::get('newpassword', [AuthController::class, 'getNewPassword'])->name('newpassword');
    Route::get('changepassword', [AuthController::class, 'getChangePassword'])->name('changepassword');
    Route::post('postcheckpassword', [AuthController::class, 'postCheckPassword'])->name('postcheckpassword');
    Route::post('postupdatepassword', [AuthController::class, 'postUpdatePassword'])->name('postupdatepassword');

    Route::get('populategradestep', 'Application\GradeStepController@populate');

    Route::get('uploadfile/{id?}', [FileController::class, 'getUpload']);
    Route::post('savefile', [FileController::class, 'postSave']);

    Route::get('fileindex', [FileController::class, 'getSearchAdmin']);

    Route::get('userdashboard', [DashboardController::class, 'getUserDashboard']);

    Route::get('bugindex', [BugController::class, 'getIndex']);
    Route::post('fetcherrordetail', [BugController::class, 'fetchDetail']);

    Route::get('submitpms', [PMSController::class, 'getIndex']);
    Route::post('uploadexcelapplicant', [PMSController::class, 'postUploadExcelApplicant']);
    Route::post('uploadexcelapplicant2', [PMSController::class, 'postUploadExcelApplicant2']);

    Route::post('submitpms', [PMSController::class, 'postSubmitPMS']);
    Route::get('viewprofile/{id?}', [ProfileController::class, 'getIndex']);

    Route::get('appraisepms', [PMSController::class, 'getAppraise']);
    Route::get('processpms/{id}', [PMSController::class, 'getProcess']);
    Route::post('processpms', [PMSController::class, 'postProcess']);
    Route::post('processpmsmultiple', [PMSController::class, 'postProcessMultiple']);
    Route::get('filedownload', [PMSController::class, 'downloadFile']);

    Route::get('sendback/{id}', [PMSController::class, 'sendBack']);

    Route::get('trackpms', [PMSController::class, 'trackPMS']);
    Route::get('resubmit/{id}', [PMSController::class, 'resubmit']);
    Route::post('resubmitpms', [PMSController::class, 'postResubmit']);
    Route::get('finalizepms/{id}', [PMSController::class, 'getFinalize']);

    Route::post('finalizepms', [PMSController::class, 'postFinalize']);
    Route::get('viewpmsdetails/{id}/{type?}', [PMSController::class, 'viewPMSDetails']);

    // remove draft scores, self scores, level 1 scores & level 2 scores
    Route::get('removeappraiseescoresubmission/{submissionId}/{laststatusId}', [PMSController::class, 'removeAppraiseeScoresOfEmployee']);
    Route::get('removeappraiserscoresubmission/{submissionId}/{laststatusId}', [PMSController::class, 'removeAppraiserScoresOfEmployee']);

    Route::get('testfinal/{id}', [PMSController::class, 'getFinalScore2']);

    Route::post('saveprofilepic', [ProFileController::class, 'saveProfilePic']);

    Route::get('pmshistory', [PMSController::class, 'getPMSHistory']);
    Route::get('empdetailsindex', [EmpDetailsController::class, 'getIndex']);
    Route::get('empdetails/{id}', [EmpDetailsController::class, 'getDetails']);
    Route::get('generateofficeorder', [PMSController::class, 'getOfficeOrderIndex']);
    Route::post('generateofficeorder', [PMSController::class, 'postGenerateOfficeOrder']);
    Route::get('emailofficeorder', [PMSController::class, 'emailOfficeOrder']);
    Route::get('officeorder/{submissionId}', [PMSController::class, 'getOfficeOrder']);
    Route::get('officeorderhistory', [PMSController::class, 'getOfficeOrderHistoryIndex']);

    Route::post('saveappraisee', [PMSController::class, 'saveAppraisee']);
    Route::post('saveappraiser', [PMSController::class, 'saveAppraiser']);

    Route::post('finaladjustment', [PMSController::class, 'finalAdjustment']);

    Route::get('disciplinaryindex', [DisciplinaryController::class, 'getIndex']);

    Route::get('fetchdepartmentemployees/{deptId}/{excludeSelf}/{json}', [Controller::class, 'getDepartmentEmployees']);
    Route::get('fetchsectionemployees/{sectionId}/{excludeSelf}/{json}', [Controller::class, 'getSectionEmployees']);

    Route::get('pmscomparisionemployees', [ReportsController::class, 'getPMSComparisionEmployees']);
    Route::get('pmscomparisionemployeesiframe', [ReportsController::class, 'getPMSComparisionEmployees']);
    Route::get('pmsscorereport', [ReportsController::class, 'getPMSScoreReport']);
    Route::get('fetchDataPMSScore', [ReportsController::class, 'getPMSScoreReportData']);

    Route::get('sectionwiseperformance', [ReportsController::class, 'getSectionWisePerformance']);
    Route::get('departmentwiseperformance', [ReportsController::class, 'getDepartmentWisePerformance']);
    Route::get('organizationalperformance', [ReportsController::class, 'getOrganizationalPerformance']);
    Route::get('audittrailreport', [ReportsController::class, 'getAuditTrailReport']);
    Route::get('eligibleforincentivereport', [ReportsController::class, 'getEligibleForIncentive']);
    Route::post('saveoutcome', [PMSController::class, 'saveOutcome']);
    Route::get('loginasemployee/{id}', [AuthController::class, 'getLoginAs']);

    Route::get('filecategoryindex', [FileController::class, 'getCategoryIndex']);
    Route::get('filecategoryinput/{id?}', [FileController::class, 'getCategoryForm']);
    Route::get('filecategorydelete/{id}', [FileController::class, 'getDeleteCategory']);
    Route::post('savefilecategory', [FileController::class, 'saveCategory']);
    Route::get('fileindex', [FileController::class, 'getFileIndex']);
    Route::get('fileinput/{id?}', [FileController::class, 'getFileForm']);
    Route::get('files', [FileController::class, 'getDisplay']);
    Route::post('savefile', [FileController::class, 'saveFile']);
    Route::get('filedelete/{id}', [FileController::class, 'getDeleteFile']);
    Route::post('filedisplay', [FileController::class, 'getRender']);

    Route::post("fetchcategoriesondepartment", [FileController::class, 'fetchCategoriesOnDept']);

    Route::get('pmsgoal', [GoalController::class, 'getList']);
    Route::get('mypmsgoal', [GoalController::class, 'getMyGoals']);
    Route::get('setgoal/{id}/{round?}', [GoalController::class, 'getIndex']);
    Route::post('savegoals', [GoalController::class, 'postSave']);
    Route::post('savegoalscore', [GoalController::class, 'postSaveScore']);

    Route::post("fetchsubordinategoals", [GoalController::class, 'fetchSubordinateGoals']);
    Route::post("fetchsubordinategoalsl2", [GoalController::class, 'fetchSubordinateGoalsL2']);

    Route::post("checkappraisersubmitted", [PMSController::class, 'checkAppraiserSubmitted']);
    Route::post('uploadkpifile', [GoalController::class, 'uploadKPIFile']);
    Route::get('correctscores', [GoalController::class, 'correctScores']);

    Route::get('getgradestep/{stepId}', [Controller::class, 'getGradeStep']);

    // update payscale and grade according to pms outcome
    Route::get('getoutcomeemployeesupdate/{lastdateofcurrentpms?}', [PMSController::class, 'getUpdateOfEmployeesUsingPmsOutcomes']);

});
