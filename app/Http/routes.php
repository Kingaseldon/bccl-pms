<?php

/*
 * SWM
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
//DEVELOMENTAL ROUTES
use App\Http\Controllers\Controller;

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
Route::get('testurl', function () {
    $object = new Controller();
    $object->testMail();
});
//END
Route::get('officesuitedashboard', 'Controller@officeSuiteDashboard');
Route::get('/', ['as' => 'login', 'middleware' => 'guest', 'uses' => 'Application\AuthController@getLogin']);
Route::get('logout', ['as' => 'logout', 'uses' => 'Application\AuthController@getLogout']);
Route::get('forgotpassword', 'Application\AuthController@forgotPassword');
Route::get('_sign_', 'Application\AuthController@getSigninFromExternal');
Route::group(['before' => 'csrf'], function () {
    Route::post('auth', ['middleware' => 'guest', 'uses' => 'Application\AuthController@postAuth']);
});

Route::get('bDay_wishes', ['uses' => 'Application\BirthdayWishController@getIndex']);

Route::group(['middleware' => 'auth'], function () {
    Route::get('checklogs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('index', ['as' => 'index', 'uses' => 'Application\DashboardController@getIndex']);
    Route::get('newpassword', ['as' => 'newpassword', 'uses' => 'Application\AuthController@getNewPassword']);
    Route::get('changepassword', ['as' => 'changepassword', 'uses' => 'Application\AuthController@getChangePassword']);
    Route::post('postcheckpassword', ['as' => 'postcheckpassword', 'uses' => 'Application\AuthController@postCheckPassword']);
    Route::post('postupdatepassword', ['as' => 'postupdatepassword', 'uses' => 'Application\AuthController@postUpdatePassword']);

    Route::get('departmentindex', ['middleware' => 'isadmin', 'uses' => 'Application\DepartmentController@getIndex']);
    Route::get('departmentinput/{id?}', ['middleware' => 'isadmin', 'uses' => 'Application\DepartmentController@getForm']);
    Route::post('savedepartment', ['middleware' => 'isadmin', 'uses' => 'Application\DepartmentController@postSave']);
    Route::get('departmentdelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\DepartmentController@getDelete']);

    Route::get('sectionindex', ['middleware' => 'isadmin', 'uses' => 'Application\SectionController@getIndex']);
    Route::get('sectioninput/{id?}', ['middleware' => 'isadmin', 'uses' => 'Application\SectionController@getForm']);
    Route::post('savesection', ['middleware' => 'isadmin', 'uses' => 'Application\SectionController@postSave']);
    Route::get('sectiondelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\SectionController@getDelete']);

    Route::get('gradestepindex', ['middleware' => 'isadmin', 'uses' => 'Application\GradeStepController@getIndex']);
    Route::get('gradestepinput/{id?}', ['middleware' => 'isadmin', 'uses' => 'Application\GradeStepController@getForm']);
    Route::post('savegradestep', ['middleware' => 'isadmin', 'uses' => 'Application\GradeStepController@postSave']);
    Route::get('gradestepdelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\GradeStepController@getDelete']);
    Route::get('populategradestep', 'Application\GradeStepController@populate');

    Route::get('designationindex', ['middleware' => 'isadmin', 'uses' => 'Application\DesignationController@getIndex']);
    Route::get('designationinput/{id?}', ['middleware' => 'isadmin', 'uses' => 'Application\DesignationController@getForm']);
    Route::post('savedesignation', ['middleware' => 'isadmin', 'uses' => 'Application\DesignationController@postSave']);
    Route::get('designationdelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\DesignationController@getDelete']);

    Route::get('hierarchyindex', ['middleware' => 'isadmin', 'uses' => 'Application\HierarchyController@getIndex']);
    Route::get('hierarchyinput/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\HierarchyController@getForm']);
    Route::post('savehierarchy', ['middleware' => 'isadmin', 'uses' => 'Application\HierarchyController@postSave']);
    Route::get('hierarchydelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\HierarchyController@getDelete']);

    Route::get('uploadfile/{id?}', 'Application\FileController@getUpload');
    Route::post('savefile', 'Application\FileController@postSave');

    Route::get('fileindex', 'Application\FileController@getSearchAdmin');

    Route::get('userdashboard', 'Application\DashboardController@getUserDashboard');

    Route::get('employeeindex', ['middleware' => 'isadmin', 'uses' => 'Application\EmployeeController@getIndex']);
    Route::get('employeeinput/{id?}', ['middleware' => 'isadmin', 'uses' => 'Application\EmployeeController@getForm']);
    Route::post('saveemployee', ['middleware' => 'isadmin', 'uses' => 'Application\EmployeeController@postSave']);
    Route::get('employeedelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\EmployeeController@getDelete']);

    Route::get('positionindex', ['middleware' => 'isadmin', 'uses' => 'Application\PositionController@getIndex']);
    Route::get('positioninput/{id?}', ['middleware' => 'isadmin', 'uses' => 'Application\PositionController@getForm']);
    Route::post('saveposition', ['middleware' => 'isadmin', 'uses' => 'Application\PositionController@postSave']);
    Route::get('positiondelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\PositionController@getDelete']);

    Route::get("criteriainput/{deptId}/{positionId}", ['middleware' => 'isadmin', 'uses' => 'Application\PositionController@fetchForm']);
    Route::post('savecriteria', ['middleware' => 'isadmin', 'uses' => 'Application\PositionController@saveCriteria']);

    Route::get("resetpassword", ['middleware' => 'isadmin', 'uses' => 'Application\EmployeeController@postResetPassword']);

    Route::get('bugindex', 'SystemAdmin\BugController@getIndex');
    Route::post('fetcherrordetail', 'SystemAdmin\BugController@fetchDetail');

    Route::get('submitpms', ['uses' => 'Application\PMSController@getIndex']);
    Route::post('uploadexcelapplicant', ['uses' => 'Application\PMSController@postUploadExcelApplicant']);
    Route::post('uploadexcelapplicant2', ['uses' => 'Application\PMSController@postUploadExcelApplicant2']);

    Route::post('submitpms', ['uses' => 'Application\PMSController@postSubmitPMS']);
    Route::get('viewprofile/{id?}', ['uses' => 'Application\ProfileController@getIndex']);

    Route::get('appraisepms', ['uses' => 'Application\PMSController@getAppraise']);
    Route::get('processpms/{id}', ['uses' => 'Application\PMSController@getProcess']);
    Route::post('processpms', ['uses' => 'Application\PMSController@postProcess']);
    Route::post('processpmsmultiple', ['uses' => 'Application\PMSController@postProcessMultiple']);
    Route::get('filedownload', 'Application\PMSController@downloadFile');

    Route::get('sendback/{id}', 'Application\PMSController@sendBack');

    Route::get('trackpms', 'Application\PMSController@trackPMS');
    Route::get('resubmit/{id}', 'Application\PMSController@resubmit');
    Route::post('resubmitpms', 'Application\PMSController@postResubmit');
    Route::get('finalizepms/{id}', 'Application\PMSController@getFinalize');

    Route::post('finalizepms', 'Application\PMSController@postFinalize');
    Route::get('viewpmsdetails/{id}/{type?}', 'Application\PMSController@viewPMSDetails');

    // remove draft scores, self scores, level 1 scores & level 2 scores
    Route::get('removeappraiseescoresubmission/{submissionId}/{laststatusId}', 'Application\PMSController@removeAppraiseeScoresOfEmployee');
    Route::get('removeappraiserscoresubmission/{submissionId}/{laststatusId}', 'Application\PMSController@removeAppraiserScoresOfEmployee');

    Route::get('testfinal/{id}', 'Application\PMSController@getFinalScore2');

    Route::post('saveprofilepic', 'Application\ProfileController@saveProfilePic');

    Route::get('pmshistory', 'Application\PMSController@getPMSHistory');
    Route::get('empdetailsindex', 'Application\EmpDetailsController@getIndex');
    Route::get('empdetails/{id}', 'Application\EmpDetailsController@getDetails');
    Route::get('generateofficeorder', 'Application\PMSController@getOfficeOrderIndex');
    Route::post('generateofficeorder', 'Application\PMSController@postGenerateOfficeOrder');
    Route::get('emailofficeorder', 'Application\PMSController@emailOfficeOrder');
    Route::get('officeorder/{submissionId}', 'Application\PMSController@getOfficeOrder');
    Route::get('officeorderhistory', 'Application\PMSController@getOfficeOrderHistoryIndex');

    Route::post('saveappraisee', 'Application\PMSController@saveAppraisee');
    Route::post('saveappraiser', 'Application\PMSController@saveAppraiser');

    Route::post('finaladjustment', 'Application\PMSController@finalAdjustment');

    Route::get('disciplinaryindex', 'Application\DisciplinaryController@getIndex');
    Route::get('disciplinaryinput/{id?}', ['middleware' => 'isadmin', 'uses' => 'Application\DisciplinaryController@getForm']);
    Route::post('savedisciplinary', ['middleware' => 'isadmin', 'uses' => 'Application\DisciplinaryController@saveDisciplinary']);
    Route::get('disciplinarydelete/{id}', ['middleware' => 'isadmin', 'uses' => 'Application\DisciplinaryController@getDelete']);

    Route::get('fetchdepartmentemployees/{deptId}/{excludeSelf}/{json}', 'Controller@getDepartmentEmployees');
    Route::get('fetchsectionemployees/{sectionId}/{excludeSelf}/{json}', 'Controller@getSectionEmployees');

    Route::get('openpms', ['middleware' => 'isadmin', 'uses' => 'Application\PMSController@getOpenPMS']);
    Route::get('openpmsprocess', ['middleware' => 'isadmin', 'uses' => 'Application\PMSController@openPMS']);
    Route::get('closepms', ['middleware' => 'isadmin', 'uses' => 'Application\PMSController@getClose']);
    Route::get('closepmsprocess', ['middleware' => 'isadmin', 'uses' => 'Application\PMSController@postClose']);

    Route::get('pmscomparisionemployees', 'Reports\ReportsController@getPMSComparisionEmployees');
    Route::get('pmscomparisionemployeesiframe', 'Reports\ReportsController@getPMSComparisionEmployees');
    Route::get('pmsscorereport', 'Reports\ReportsController@getPMSScoreReport');
    Route::get('fetchDataPMSScore', 'Reports\ReportsController@getPMSScoreReportData');

    Route::get('sectionwiseperformance', 'Reports\ReportsController@getSectionWisePerformance');
    Route::get('departmentwiseperformance', 'Reports\ReportsController@getDepartmentWisePerformance');
    Route::get('organizationalperformance', 'Reports\ReportsController@getOrganizationalPerformance');
    Route::get('audittrailreport', 'Reports\ReportsController@getAuditTrailReport');
    Route::post('saveoutcome', 'Application\PMSController@saveOutcome');
    Route::get('loginasemployee/{id}', 'Application\AuthController@getLoginAs');

    Route::get('filecategoryindex', 'Files\FileController@getCategoryIndex');
    Route::get('filecategoryinput/{id?}', 'Files\FileController@getCategoryForm');
    Route::get('filecategorydelete/{id}', 'Files\FileController@getDeleteCategory');
    Route::post('savefilecategory', 'Files\FileController@saveCategory');
    Route::get('fileindex', 'Files\FileController@getFileIndex');
    Route::get('fileinput/{id?}', 'Files\FileController@getFileForm');
    Route::get('files', 'Files\FileController@getDisplay');
    Route::post('savefile', 'Files\FileController@saveFile');
    Route::get('filedelete/{id}', 'Files\FileController@getDeleteFile');
    Route::post('filedisplay', 'Files\FileController@getRender');

    Route::post("fetchcategoriesondepartment", 'Files\FileController@fetchCategoriesOnDept');

    Route::get('pmsgoal', 'Application\GoalController@getList');
    Route::get('mypmsgoal', 'Application\GoalController@getMyGoals');
    Route::get('setgoal/{id}/{round?}', 'Application\GoalController@getIndex');
    Route::post('savegoals', 'Application\GoalController@postSave');
    Route::post('savegoalscore', 'Application\GoalController@postSaveScore');

    Route::post("fetchsubordinategoals", 'Application\GoalController@fetchSubordinateGoals');
    Route::post("fetchsubordinategoalsl2", 'Application\GoalController@fetchSubordinateGoalsL2');

    Route::post('uploadkpifile', 'Application\GoalController@uploadKPIFile');

    Route::get('getgradestep/{stepId}', 'Controller@getGradeStep');

    // update payscale and grade according to pms outcome
    Route::get('getoutcomeemployeesupdate/{lastdateofcurrentpms?}', 'Application\PMSController@getUpdateOfEmployeesUsingPmsOutcomes');

});
view()->composer(
    ['master'],
    'App\Http\ViewComposers\MasterComposer'
);
