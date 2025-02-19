<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;

use App\Models\GradeStep;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\Controller;

class GradeStepController extends Controller
{
    public function getIndex(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $perPage = 12;
        $name = $request->input('Name');
        $gradeId = $request->input('GradeId');
        $status = $request->Status;

        $condition = "1=1";
        $parameters = [];

        if ($name) {
            $condition .= " and T1.Name like ?";
            $parameters[] = "%$name%";
        }
        if ($gradeId) {
            $condition .= " and T1.GradeId = ?";
            $parameters[] = $gradeId;
        }
        if ($status != '') {
            $condition .= " and coalesce(T1.Status,0) = ?";
            $parameters[] = (int) $status;
        }

        $gradesteps = DB::table('mas_gradestep as T1')
            ->whereRaw("$condition", $parameters)
            ->select('T1.Id', 'T1.PayScale', 'T1.Name as GradeStep', DB::raw("case when coalesce(T1.Status,0) = 1 then 'Active' else 'Inactive' end as Status"))
            ->orderBy('T1.Status', 'DESC')
            ->orderBy("T1.StartingSalary", "DESC")
            ->paginate($perPage);
        $grades = DB::select("select Id, Name from mas_grade where coalesce(IsManagerialRole,0) = 0 and Id <> 9 order by Name");
        return view('application.gradestepindex', ['gradesteps' => $gradesteps, 'perPage' => $perPage, 'grades' => $grades]);
    }

    public function getForm($id = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $setting = DB::table("sys_settings")->where("Id", CONST_SYSSETTING_PAYSCALETYPE)->value("Value");
        $gradestep = [new GradeStep()];
        $grades = DB::select("select Id, Name from mas_grade where coalesce(IsManagerialRole,0) = 0 and Id <> 9 order by Name");
        $update = false;
        if ($id) {
            $update = true;
            $gradestep = GradeStep::find($id);
            if (!$gradestep) {
                abort(404);
            }
        }
        return view('application.gradestepform')
            ->with('setting', $setting)
            ->with('grades', $grades)
            ->with('gradestep', $gradestep)
            ->with('update', $update);
    }

    public function postSave(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        //FORM VALIDATION
        $this->validate(
            $request,
            [
                'Name' => 'required',
            ],
            [
                'Name.required' => 'Please type a Name',
            ]
        );
        //END
        $inputs = $request->input();
        if (!$inputs['PayScale']) {
            $inputs['PayScale'] = NULL;
        }
        $save = false;
        DB::beginTransaction();
        try {
            if ($inputs['Id']) {
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::id();
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = GradeStep::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if (!(count($changes) == 2 && array_key_exists('updated_at', $changes) && array_key_exists('EditedBy', $changes)) && !(count($changes) == 1 && array_key_exists('updated_at', $changes))) {
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("insert into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", ['mas_gradestep', Auth::id(), 0, $recordJson]);
                }
                $object->update();
            } else {
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::id();
                unset($inputs['Id']);
                $save = true;
                $savedRecord = GradeStep::create($inputs);
                $id = $savedRecord->Id;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->with('errormessage', $e->getMessage());
        }

        if ($inputs['PayScale']) {
            $this->updateSalaryDetailsForGradeStep($id);
        }
        DB::commit();
        if (isset($saveAudit) && $saveAudit) {
            $this->saveAuditTrail('mas_gradestep', $id);
        }
        return redirect('gradestepindex')->with('successmessage', 'Record has been ' . ($save ? 'saved' : 'updated') . '!');
    }

    public function getDelete($id): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->saveAuditTrail('mas_gradestep', $id, 1);
            GradeStep::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "GradeStep could not be deleted because there are Employees or other records related to this gradestep.");
        }
        return back()->with('successmessage', 'Record has been deleted');
    }

    public function populate()
    {
        for ($i = 1; $i <= 12; $i++):
            $text = "T2 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::id();
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 12; $i++):
            $text = "T1 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::id();
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 9; $i++):
            $text = "P2 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::id();
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 12; $i++):
            $text = "P1 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::id();
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 3; $i++):
            $text = "Exe Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::id();
            GradeStep::create($inputs);
        endfor;
    }

    public function updateSalaryDetailsForGradeStep($recordId = null): bool
    {
        $setting = DB::table("sys_settings")->where('Id', CONST_SYSSETTING_PAYSCALETYPE)->value("Value");
        if ($recordId) {
            $gradeSteps = DB::select("select Id, PayScale from mas_gradestep where PayScale is not null and Id = ?", [$recordId]);
        } else {
            $gradeSteps = DB::select("select Id, PayScale from mas_gradestep where PayScale is not null");
        }
        foreach ($gradeSteps as $gradeStep):
            $id = $gradeStep->Id;
            $payScale = $gradeStep->PayScale;
            if ($payScale) {
                if ((int) $setting === 1) {
                    $payScaleArrayFormat = explode("-", $payScale);
                    $startingPay = trim($payScaleArrayFormat[0]);
                    $firstIncrement = trim($payScaleArrayFormat[1]);
                    $lastPay = trim($payScaleArrayFormat[2]);
                    DB::table('mas_gradestep')->where('Id', $id)->update([
                        'StartingSalary' => $startingPay,
                        'Increment' => $firstIncrement,
                        'EndingSalary' => $lastPay
                    ]);
                } else {
                    $payScaleArrayFormat = explode("-", $payScale);
                    $startingPay = trim($payScaleArrayFormat[0]);
                    $firstIncrement = trim($payScaleArrayFormat[1]);
                    $middlePay = trim($payScaleArrayFormat[2]);
                    $secondIncrement = trim($payScaleArrayFormat[3]);
                    $lastPay = trim($payScaleArrayFormat[4]);
                    DB::table('mas_gradestep')->where('Id', $id)->update([
                        'StartingSalary' => $startingPay,
                        'FirstIncrement' => $firstIncrement,
                        'MiddleSalary' => $middlePay,
                        'SecondIncrement' => $secondIncrement,
                        'EndingSalary' => $lastPay
                    ]);
                }
            }
        endforeach;
        return true;
    }

}
