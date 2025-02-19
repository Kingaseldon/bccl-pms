@extends('master')
@section('page-title','Appraise PMS')
@section('page-header',"Appraise PMS")
@section('pagescripts')
    <script>
        $( function() {
            $( "#accordion" ).accordion({
                collapsible: true,
                heightStyle: "content",
                active: false
            });
        } );
    </script>
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12">
                                @if(isset($status) && ($status == 2 || $status == 3))
                                    <p style="color:#fff;"><strong>{!! trim($closedMessage) !!}</strong></p>
                                    <br/>
                                    {{-- Update Employee PMS Outcome Details  --}}
                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="{{URL::to('getoutcomeemployeesupdate', [$endingDateOfCurrentPMS])}}" class="btn btn-danger"><i class="fa fa-refresh"></i>&nbsp; Update Employee (PMS Outcome)</a>
                                        </div>
                                    </div>
                                @else
                                    @if($type == 1)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-condensed font-small" style="margin-bottom:0;">
                                                <thead>
                                                    <tr>
                                                        <th style="width:20px">Sl.#</th>
                                                        <th>Employee</th>
                                                        <th>Status</th>
                                                        <th>View</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php $slNo = 1; ?>
                                                @forelse($employees as $employee)
                                                    <tr>
                                                        <td>{{$slNo++}}</td>
                                                        <td><a href="{{url('viewprofile',[$employee->Id])}}" target="_blank"><strong>{{$employee->Name}}</strong></a> - ({{$employee->Designation}}), {{$employee->Position}}</td>
                                                        <td class="text-center" style="font-weight:bold; padding: 0.3rem 0.4rem 0.4rem 0.4rem;">
                                                            @if((bool)$employee->Status && (($employee->LastStatusId != CONST_PMSSTATUS_SUBMITTED) && ($employee->LastStatusId != CONST_PMSSTATUS_DRAFT) && ($employee->LastStatusId != CONST_PMSSTATUS_SENTBACKBYVERIFIER)))
                                                                <span style="font-weight:bold;color:#3bc927;">
                                                            @endif
                                                            {!! (bool)$employee->Status?(((bool)$employee->PMSOutcomeId)?'Completed':(($employee->LastStatusId == CONST_PMSSTATUS_SUBMITTED || $employee->LastStatusId == CONST_PMSSTATUS_DRAFT || $employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER || $employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER)?$employee->Status:'Appraised')):"<span style='color:#df5f5f'>PMS Not Submitted</span>" !!}
                                                            @if(!(bool)$employee->PMSOutcomeId)
                                                                @if($employee->LastStatusId == CONST_PMSSTATUS_SUBMITTED)
{{--                                                                    to {{$employee->Level1Position}}--}}
                                                                @elseif($employee->LastStatusId == CONST_PMSSTATUS_VERIFIED)
{{--                                                                    by {{$employee->Level1Position}}--}}
                                                                @elseif($employee->LastStatusId == CONST_PMSSTATUS_APPROVED)
                                                                    @if(isset($employee->NoLevel2) && $employee->NoLevel2 == 1)
{{--                                                                        by {{$employee->Level1Position}}--}}
                                                                    @else
{{--                                                                        by {{$employee->Level2Position}}--}}
                                                                    @endif
                                                                @elseif($employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER)
                                                                    {{--@if(isset($employee->NoLevel2) && $employee->NoLevel2 == 1)--}}
                                                                        {{--by {{$employee->Level1Position}}--}}
                                                                    {{--@else--}}
                                                                        {{--by {{$employee->Level2Position}}--}}
                                                                    {{--@endif--}}
                                                                @elseif($employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER)
{{--                                                                    by {{$employee->Level1Position}}--}}
                                                                @elseif($employee->LastStatusId == CONST_PMSSTATUS_DRAFT)
{{--                                                                    by {{$employee->LastStatusByEmployee}}--}}
                                                                @endif
                                                            @endif
                                                            @if((bool)$employee->Status && (($employee->LastStatusId != CONST_PMSSTATUS_SUBMITTED) || ($employee->LastStatusId != CONST_PMSSTATUS_DRAFT) || ($employee->LastStatusId != CONST_PMSSTATUS_SENTBACKBYVERIFIER)))
                                                                </span>
							    @endif

                                                            <?php $isAtDesk = false; ?>
							    @if($employee->ReportingLevel1EmployeeId == Auth::user()->Id)
                                                                @if(($employee->LastStatusId == CONST_PMSSTATUS_SUBMITTED) || ($employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER) || ($employee->LastStatusId==CONST_PMSSTATUS_DRAFT && $employee->StatusByEmployeeId == Auth::user()->Id))
                                                                    @if($employee->MultipleStatus != 1)
                                                                        <?php $isAtDesk = true; ?>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                            @if($employee->ReportingLevel2EmployeeId == Auth::user()->Id)
                                                                @if($employee->LastStatusId == CONST_PMSSTATUS_VERIFIED || ($employee->LastStatusId==CONST_PMSSTATUS_DRAFT && $employee->StatusByEmployeeId == Auth::user()->Id))
                                                                    @if($employee->MultipleStatus != 1)
                                                                        <?php $isAtDesk = true; ?>
                                                                    @endif
                                                                @endif
                                                            @endif
							    <div class="hide">{{$isAtDesk?1:0}}</div>
                                                            @if($isAtDesk)
                                                                <i class="fa fa-chevron-right"></i> <a href="{{url('processpms',[$employee->SubmissionId])}}" class="process-application btn btn-xs btn-warning">Evaluate</a>
							    @endif	
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{url('viewpmsdetails',[$employee->SubmissionId,2])}}" class="btn btn-xs btn-primary @if(!(bool)$employee->Status){{"disabled"}}@endif" target="_blank" >View</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4"><center>No data to display!</center></td>
                                                    </tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <?php $notReadyForFinalAction = false; ?>
                                        @if(Auth::user()->RoleId == 1 && ($finalScoreNotApplied > 0) || ($pmsNotSubmitted > 0))
                                            <?php $notReadyForFinalAction = true; ?>
                                            <div class="row">
                                                <div class="col-12">
                                                    @if($pmsNotSubmitted == 0 && $finalScoreNotApplied > 0)
                                                        <button type="button" class="btn btn-warning btn-xs">Warning</button><br><span class="h6" style="color:#fff;"> Please apply final adjustment.</span>
                                                        <br>
                                                    @else
                                                        <button type="button" class="btn btn-warning btn-xs">Warning</button><br><span class="h6" style="color:#fff;"> {{$pmsNotSubmitted}} employees have still not submitted PMS evaluation. Once PMS of all employees has been evaluated, revenue score adjustment can be applied.</span>
                                                        <br>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        <div id="accordion">
                                            @foreach($units as $unit)
                                                <h6><strong>@if($type == 2){{$unit->Department}} | @endif {{$unit->Name}} -</strong> <span style="font-weight:bold;color:#00f10a">Completed ({{$unit->Completed}})</span>, <span style="font-weight:bold;color:#ffc107">Pending Your Action ({{$unit->Pending}})</span>, <span style="font-weight:bold;color:#df5f5f;">PMS Not Submitted ({{$unit->NotSubmitted}})</span></h6>
                                                <div>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-condensed large-padding font-small" style="margin-bottom:0;">
                                                            <thead>
                                                            <tr>
                                                                <th style="width:20px;">Sl.#</th>
                                                                <th>Employee</th>
                                                                @if($type == 3)
                                                                    <th>Section</th>
                                                                @endif
                                                                <th>Status</th>
                                                                <th>View</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php $slNo = 1; ?>
                                                            @forelse($employees[$unit->Id] as $employee)
                                                                <tr>
                                                                    <td>{{$slNo++}}</td>
                                                                    <td><a href="{{url('viewprofile',[$employee->Id])}}" target="_blank"><strong>{{$employee->Name}}</strong></a> ({{$employee->Designation}}), {{$employee->Position}} (EmpId {{$employee->EmpId}})</td>
                                                                    @if($type==3)<td>{{$employee->Section}}</td>@endif
                                                                    <td class="text-center" style="font-weight:bold; padding: 0.3rem 0.4rem 0.4rem 0.4rem;">

                                                                        @if(Auth::user()->RoleId == 1)
                                                                            <?php $checkStatuses = [CONST_PMSSTATUS_APPROVED]; ?>
                                                                            <?php $completedStatus = ''; ?>
                                                                        @else
                                                                            @if($employee->Level == 1)
                                                                                @if(!isset($employee->NoLevel2) || (isset($employee->NoLevel2) && $employee->NoLevel2 == 0))
                                                                                    <?php $checkStatuses = [CONST_PMSSTATUS_SUBMITTED,CONST_PMSSTATUS_SENTBACKBYAPPROVER]; ?>
                                                                                    <?php $completedStatus = CONST_PMSSTATUS_VERIFIED; ?>
                                                                                @else
                                                                                    <?php $checkStatuses = [CONST_PMSSTATUS_SUBMITTED]; ?>
                                                                                    <?php $completedStatus = CONST_PMSSTATUS_APPROVED; ?>
                                                                                @endif
                                                                            @else
                                                                                <?php $checkStatuses = [CONST_PMSSTATUS_VERIFIED]; ?>
                                                                                <?php $completedStatus = CONST_PMSSTATUS_APPROVED; ?>
                                                                            @endif
                                                                        @endif

                                                                        @if(Auth::user()->RoleId == 1)
                                                                            @if((bool)$employee->PMSOutcomeId)
                                                                                <span style="font-weight:bold;color:#3bc927;">
                                                                            @endif
                                                                        @else
                                                                            @if($employee->LastStatusId == $completedStatus)
                                                                                <span style="font-weight:bold;color:#3bc927;">
                                                                            @endif
                                                                        @endif
                                                                        {!! (bool)$employee->Status?(((bool)$employee->PMSOutcomeId)?'Completed':(($employee->LastStatusId == CONST_PMSSTATUS_SUBMITTED || $employee->LastStatusId == CONST_PMSSTATUS_DRAFT || $employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER || $employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER)?$employee->Status:((isset($employee->PMSOutcomeDraft) && $employee->PMSOutcomeDraft == 1)?"Completed":'Appraised'))):"<span style='color:#df5f5f'>PMS Not Submitted</span>" !!}
                                                                        @if(!(bool)$employee->PMSOutcomeId)
                                                                            @if($employee->LastStatusId == CONST_PMSSTATUS_SUBMITTED)
                                                                                {{--to {{$employee->Level1Position}}--}}
                                                                            @elseif($employee->LastStatusId == CONST_PMSSTATUS_VERIFIED)
                                                                                {{--by {{$employee->Level1Position}}--}}
                                                                            @elseif($employee->LastStatusId == CONST_PMSSTATUS_APPROVED)
                                                                                {{--@if(Auth::user()->RoleId != 5 )--}}
                                                                                {{--@if(isset($employee->NoLevel2) && $employee->NoLevel2 == 1)--}}
                                                                                {{--by {{$employee->Level1Position}}--}}
                                                                                {{--@else--}}
                                                                                {{--by {{$employee->Level2Position}}--}}
                                                                                {{--@endif--}}
                                                                                {{--@endif--}}
                                                                            @elseif($employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER)
                                                                                @if(isset($employee->NoLevel2) && $employee->NoLevel2 == 1)
                                                                                    {{--by {{$employee->Level1Position}}--}}
                                                                                @else
                                                                                    {{--by {{$employee->Level2Position}}--}}
                                                                                @endif
                                                                            @elseif($employee->LastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER)
                                                                                {{--by {{$employee->Level1Position}}--}}
                                                                            @elseif($employee->LastStatusId == CONST_PMSSTATUS_DRAFT)
                                                                                {{--by {{$employee->LastStatusByEmployee}}--}}
                                                                            @endif
                                                                        @endif
                                                                        @if(Auth::user()->RoleId == 1)
                                                                            @if((bool)$employee->PMSOutcomeId)
                                                                                </span>
                                                                            @endif
                                                                        @else
                                                                        @if($employee->LastStatusId == $completedStatus)
                                                                            </span>
                                                                        @endif
                                                                    @endif
                                                                    @if((in_array($employee->LastStatusId,$checkStatuses) && !(bool)$employee->PMSOutcomeId)||($employee->LastStatusId==CONST_PMSSTATUS_DRAFT && $employee->StatusByEmployeeId == Auth::user()->Id))
                                                                        @if(!$notReadyForFinalAction && $employee->PMSOutcomeDraft == 0)
                                                                            <i class="fa fa-chevron-right"></i> <a href="{{url($employee->LastStatusId==CONST_PMSSTATUS_APPROVED?'finalizepms':'processpms',[$employee->SubmissionId])}}" class="process-application btn btn-xs btn-warning">@if(Auth::user()->RoleId == 1)@if($employee->PMSOutcomeDraft == 0){{(bool)$employee->PMSOutcomeId ? "Update":"Process"}}@endif @else{{"Evaluate"}}@endif</a>
                                                                            &nbsp; @if(Auth::user()->RoleId == 1)({{$employee->SavedOutcome}})@endif
                                                                        @endif
                                                                    @endif
                                                                    </td>
                                                                    <td class="text-center">
									<a href="{{url('viewpmsdetails',[$employee->SubmissionId,2])}}" class="btn btn-xs btn-primary @if(!(bool)$employee->Status){{"disabled"}}@endif" target="_blank" >View</a> &nbsp;
									@if (($employee->LastStatusId == CONST_PMSSTATUS_DRAFT) || ($employee->LastStatusId == CONST_PMSSTATUS_SUBMITTED))
                                                                            <a href="{{url('removeappraiseescoresubmission', [$employee->SubmissionId, $employee->LastStatusId])}}" class="btn btn-xs btn-warning appraiseeconfirm @if(!(bool)$employee->Status){{"disabled"}}@endif" >Appraisee Scores</a> &nbsp;
                                                                        @endif
                                                                        @if (($employee->LastStatusId == CONST_PMSSTATUS_VERIFIED) || ($employee->LastStatusId == CONST_PMSSTATUS_APPROVED))
                                                                            <a href="{{url('removeappraiserscoresubmission', [$employee->SubmissionId, $employee->LastStatusId])}}" class="btn btn-xs btn-danger appraiserconfirm @if(!(bool)$employee->Status){{"disabled"}}@endif" >Appraiser Scores</a> &nbsp;
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="{{$type==3?5:4}}"><center>No data to display!</center></td>
                                                                </tr>
                                                            @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

{{--                                    @if(Auth::user()->RoleId == 1 && $notWithinPMS)--}}
{{--                                    @if(Auth::user()->RoleId == 1)--}}
                                    @if(Auth::user()->RoleId == 99)
                                        <br>
                                        <div class="row">
                                            <div class="col-md-4">
                                                {{Form::open(['url'=>'finaladjustment'])}}
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="TargetRevenue">Target Revenue <span class="required">*</span></label>
                                                            <input type="number" id="TargetRevenue" onkeydown="return event.keyCode !== 69" step="any" value="{{empty($revDetails)?'':$revDetails[0]->TargetRevenue}}" required="required" name="TargetRevenue" autocomplete="off" class="form-control"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="AchievedRevenue">Revenue Achieved <span class="required">*</span></label>
                                                            <input type="number" id="AchievedRevenue" onkeydown="return event.keyCode !== 69" step="any" value="{{empty($revDetails)?'':$revDetails[0]->AchievedRevenue}}" required="required" name="AchievedRevenue" autocomplete="off" class="form-control"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="FinalAdjustmentPercent">Final Adjustment (out of) <span class="required">*</span></label>
                                                            <input type="number" id="FinalAdjustmentPercent" onkeydown="return event.keyCode !== 69" step="any" max="50" value="{{(!count($finalAdjustmentPercent))?'':$finalAdjustmentPercent[0]}}" required="required" name="FinalAdjustmentPercent" autocomplete="off" class="form-control"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="calculated">Final Adjustment (Calculated)</label>
                                                            <?php $calculated = ''; ?>
                                                            @if(!(empty($revDetails) && empty($finalAdjustmentPercent)))
                                                                <?php
                                                                    if((bool)$revDetails[0]->AchievedRevenue){
                                                                        $calculated = round(((doubleval($revDetails[0]->AchievedRevenue)/doubleval($revDetails[0]->TargetRevenue)) * doubleval($finalAdjustmentPercent[0])),2);
                                                                        $calculated = $calculated > $finalAdjustmentPercent[0] ? $finalAdjustmentPercent[0]: $calculated;
                                                                    }
                                                                ?>
                                                            @endif
                                                            <input type="number" disabled="disabled" onkeydown="return event.keyCode !== 69" step="any" max="50" autocomplete="off" value="{{$calculated}}" required="required" id="calculated" class="form-control"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-success"><i class="fa fa-calculator"></i> Compute Adjustment</button>
                                                {{Form::close()}}
                                            </div>
                                        </div>
                                    @endif
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

