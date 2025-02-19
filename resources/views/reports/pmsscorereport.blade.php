@extends('master')
@section('page-title','PMS Result')
@section('page-header',"PMS Result")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;min-height:2800px;">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">PMS Result</a>
                                <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">PMS Comparision Report</a>
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                                    </div>
                                </div>

                                <form action="{{Request::url()}}" id="param-form" method="GET">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-2">
                                            <div class="form-group">
                                                <label for="DepartmentId" class="control-label">Department <span class="required">*</span></label>
                                                <select name="DepartmentId" required="required" class="form-control select2 fetch-employee-on-dept" id="filter-section">
                                                    <option value="">--SELECT ONE--</option>
                                                    @foreach($departments as $department)
                                                        <option @if($department->Id == app('request')->input('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->Name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-2">
                                            <div class="form-group">
                                                <label for="SectionId" class="control-label">Section</label>
                                                <select name="SectionId" class="form-control select2 fetch-employee-on-section" id="select-department">
                                                    <option value="">All</option>
                                                    @foreach($sections as $section)
                                                        <option @if(app('request')->has('DepartmentId') && $section->DepartmentId != app('request')->input('DepartmentId'))class="hide" disabled="disabled"@endif data-departmentid="{{$section->DepartmentId}}" @if($section->Id == app('request')->input('SectionId'))selected="selected"@endif value="{{$section->Id}}">{{$section->Name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div class="form-group">
                                                <label for="EmployeeId" class="control-label">Employee</label>
                                                <select name="EmployeeId[]" multiple="multiple" class="form-control select2" id="fetched-employees">
                                                    <option value="">All</option>
                                                    @foreach($employees as $employee)
                                                        <option @if(in_array($employee->Id,app('request')->has('EmployeeId')?app('request')->input('EmployeeId'):[]))selected="selected"@endif value='{{$employee->Id}}'>{{$employee->Name}} ({{$employee->Designation}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="PMSPeriod" class="control-label">PMS Period</label>
                                                <select name="PMSPeriod[]" class="form-control select2 select2multiple" id="PMSPeriod" multiple>
                                                    @foreach($pmsPeriods as $pmsPeriod)
                                                        <option @if(in_array($pmsPeriod->Id,empty(app('request')->input('PMSPeriod'))?[]:app('request')->input('PMSPeriod')))selected="selected"@endif value="{{$pmsPeriod->Id}}">{{date_format(date_create($pmsPeriod->StartDate),"M, Y")}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-2">
                                            <div class="form-group">
                                                <label for="FromYear" class="control-label">From Year</label>
                                                <select name="FromYear" class="form-control select2 clear-pms-period" id="FromYear">
                                                    <option value="">--SELECT--</option>
                                                    @foreach($pmsYears as $pmsYear)
                                                        <option @if($pmsYear->Year == app('request')->input('FromYear'))selected="selected"@endif value="{{$pmsYear->Year}}">{{$pmsYear->Year}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-2">
                                            <div class="form-group">
                                                <label for="ToYear" class="control-label">To Year</label>
                                                <select name="ToYear" class="form-control select2 clear-pms-period" id="ToYear">
                                                    <option value="">--SELECT--</option>
                                                    @foreach($pmsYears as $pmsYear)
                                                        <option @if($pmsYear->Year == app('request')->input('ToYear'))selected="selected"@endif value="{{$pmsYear->Year}}">{{$pmsYear->Year}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" value="1" name="Submitted"/>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-5 col-8">
                                        <div class="row">
                                            <button type="submit" id="submit-form" style="" class="dont-disable btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('pmsscorereport')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a> &nbsp;
                                            <?php $append = ''; ?>
                                            <?php
                                                $serverUri = $_SERVER['REQUEST_URI'];
                                                $replaced = str_replace("/pmsscorereport",'',$serverUri);
                                                if(strpos($replaced,"export=excel") == false){
                                                    if($replaced == ""){
                                                        $append = "";
                                                    }else{
                                                        $append = $replaced."&export=excel";
                                                    }
                                                }
                                            ?>
                                            <?php //$params.="export=excel"; ?>
                                            <a href="{{Request::url()."$append"}}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> &nbsp;Export to Excel</a>
                                        </div>
                                    </div>
                                    @if(Auth::user()->RoleId == 1)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="FinalOutcomeId" class="control-label">Action</label>
                                                <select name="PMSOutcomeId" id="FinalOutcomeId" class="form-control select2">
                                                    @foreach($outcomes as $outcome)
                                                        <option value="{{$outcome->Id}}" @if($outcome->Id == app('request')->input('PMSOutcomeId'))selected="selected"@endif >{{$outcome->Name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                        <br>
                                    @endif
                                </form>


                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="sticky-columns table-responsive">
                                            <table id="table-to-clone"class="sticky-columns table table-bordered table-striped table-hover table-condensed">
                                                <thead>
                                                <tr>
                                                    <th class="headcol" colspan="4">
                                                        <div style="width:41%">Employee</div>
                                                        <div style="width:18%">DoA</div>
                                                        <div style="width:27%">Duration of Service</div>
                                                        <div style="width:14%">Basic Pay</div>
                                                    </th>
                                                    <th>CID</th>
                                                    {{--<th>Emp Id</th>--}}
                                                    <th>Dept.</th>
                                                    <th>Designation</th>
                                                    <th>Work Location</th>
                                                    <th>Grade</th>
                                                    {{--<th>DoA</th>--}}
                                                    {{--<th>Duration of Service</th>--}}
                                                    {{--<th>Basic Pay</th>--}}
                                                    <th>Section</th>
                                                    @foreach($pmsPeriods as $pmsPeriod)
                                                        @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                                                            <th>{{date_format(date_create($pmsPeriod->StartDate),'M, Y')}}</th>
                                                            <th>Result</th>
                                                            <th>Remarks</th>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                                </thead>
                                                @forelse($result as $data)
                                                    <tr>
                                                        <td class="headcol" width="500">
                                                            <div style="width:41%">
                                                                @foreach($pmsPeriods as $pmsPeriod)
                                                                    @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                                                                        <?php $idVar = $pmsPeriod->Id." Id"; $id = $data->$idVar; $submissionIdVar = $pmsPeriod->Id." SubmissionId"; $submissionId = $data->$submissionIdVar; ?>
                                                                    @endif
                                                                @endforeach
                                                                @if((bool)$id)
                                                                    <span class="pull-left select-container @if(!app('request')->has('PMSOutcomeId') || app('request')->input('PMSOutcomeId')==1){{"hide"}}@endif"><input type="checkbox" class="select-employee"/></span> &nbsp;
                                                                @endif
                                                                <span class="pull-left remove-container hide"><i class="remove-employee fa fa-times" style="color:#e5781f;"></i></span> &nbsp;
                                                                <a target="_blank" href="{{url('viewprofile',[$data->Id])}}"><strong>{{$data->Employee}}</strong></a> ({{$data->EmpId}})
                                                                <input type="hidden" name="Id[]" class="emp-id" disabled="disabled" value="{{$data->Id}}"/>
                                                                <input type="hidden" name="SubmissionId[]" class="submission-id" disabled="disabled" value="{{$submissionId}}"/>
                                                                <input type="hidden" name="OutcomeId[]" class="outcome-id" disabled="disabled" value="{{$data->Id}}"/>
                                                                <input type="hidden" class="SavedPMSOutcomeId" value="{{$data->SavedPMSOutcomeId}}"/>
                                                            </div>
                                                            <div style="width:18%">
                                                                {{$data->DateOfAppointment}}
                                                            </div>
                                                            <div style="width:27%">
                                                                <?php
                                                                $dateOfRegularization = new DateTime($data->DateOfRegularization);
                                                                $now = new DateTime();
                                                                $diff = $dateOfRegularization->diff($now);
                                                                $diffYears = $diff->format("%y");
                                                                $diffMonths = $diff->format("%m");
                                                                ?>
                                                                @if($now > $dateOfRegularization)
                                                                    @if($diffYears > 0)
                                                                        @if($diffMonths == 0)
                                                                            {!! $diff->format("%y Years") !!}
                                                                        @else
                                                                            {!! $diff->format("%y Years and %m Months") !!}
                                                                        @endif
                                                                    @else
                                                                        @if($diffMonths > 0)
                                                                            {{$diff->format("%m Months")}}
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </div>
                                                            <div style="width:14%">{{$data->BasicPay}}</div>
                                                        </td>
                                                        <td>{{$data->CIDNo}}</td>
                                                        <td>{{$data->Department}}</td>
                                                        <td>{{$data->Designation}}</td>
                                                        <td>{{$data->JobLocation}}</td>
                                                        <td>{{$data->GradeStep}}</td>
                                                        <td>{{$data->Section}}</td>
                                                        @foreach($pmsPeriods as $pmsPeriod)
                                                            @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                                                                <?php $scoreVar = $pmsPeriod->Id." Score"; $resultVar = $pmsPeriod->Id." Result"; $remarksVar = $pmsPeriod->Id." Remarks"; ?>
                                                                <td>{{$data->$scoreVar}}</td>
                                                                <td>{{$data->$resultVar}}</td>
                                                                <td>{{$data->$remarksVar}}</td>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                @empty
                                                    @if(app('request')->has('DepartmentId'))
                                                        <tr><td class="headcol">&nbsp;</td><td colspan="90">No data to display</td></tr>
                                                    @else
                                                        <tr><td class="headcol">&nbsp;</td><td colspan="90">Please select a department and click search to view this report</td></tr>
                                                    @endif

                                                @endforelse
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                {{Form::open(['url'=>'saveoutcome','id'=>'outcome-form'])}}
                                <div id="selected-employees">

                                </div>
                                <button type="submit" name="SubmitType" value="1" class="btn btn-warning hide" id="outcome-save-button">Save as Draft</button> &nbsp; <button name="SubmitType" value="2" type="submit" class="btn btn-success hide" id="outcome-submit-button">Submit</button>
                                {{Form::close()}}
                                <br><br>
                            </div>
                            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                <br>
                                <iframe frameborder="0" scrolling="no" onload="resizeIframe(this)" src="{{url('pmscomparisionemployeesiframe')}}" style="width:100%;min-height:450px; padding-left:0;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('pagescripts')
    <script>
        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
        }
    </script>
@endsection
