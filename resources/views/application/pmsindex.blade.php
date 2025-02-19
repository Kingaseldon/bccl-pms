@extends('master')
@section('page-title','Submit PMS')
@section('page-header','Submit PMS')
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-8 card dashboard-product">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(Session::has('message'))
                            <h6><i class="fa fa-times-circle" style="color:red"></i> {!!Session::get('message')!!}</h6>
                        @endif

                        @if($status == 2 || $status == 3)
                            <div class="row">
                                <div class="col-md-12">
                                    {!! trim($closedMessage) !!}
                                </div>
                            </div>
                        @else
                            @if(!$hasWeightageDefined || !$hasAssessmentAreasDefined)
                                <strong>Your assessment areas and weightage are not defined. Please contact administrator or HR department</strong>
                            @else
                                @if(!$alreadySubmitted && !$notWithinPMSPeriod)
                                    {{Form::open(['url'=>'submitpms','files'=>true])}}
                                    {{Form::hidden('Id',isset($position['Id'])?$position['Id']:old('Id'))}}
                                    {{Form::hidden('PositionId',$userPositionId)}}
                                    {{Form::hidden('DepartmentId',$userDepartmentId)}}
                                    {{Form::hidden('WeightageForLevel1',$weightage[0]->WeightageForLevel1)}}
                                    {{Form::hidden('WeightageForLevel2',$weightage[0]->WeightageForLevel2)}}
                                    {{Form::hidden('Level2CriteriaType',$weightage[0]->Level2CriteriaType)}}
                                    <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="ExcelApplicant">Goals/Targets Upload file [5MB Max]</label>
                                                    <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="ExcelApplicant" name="File" value="{{isset($position['Name'])?$position['Name']:old('Name')}}" autocomplete="off" class="form-control file-xs"/>
                                                </div>
                                            </div>
                                        <div class="col-md-6 offset-2">
                                            <div class="form-group">
                                                <label for="SupportingDoc">Supporting document for Additional Achievement [5MB Max]</label>
                                                <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="SupportingDoc" name="File2" value="{{isset($position['Name'])?$position['Name']:old('Name')}}" autocomplete="off" class="form-control file-xs"/>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $average = 0; ?>
                                    @if(!empty($scoresOfSubordinate))
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-condensed">
                                                <thead>
                                                <tr>
                                                    <th style="width:20px;">Sl #</th>
                                                    <th>Section</th>
                                                    <th>Assessment Area</th>
                                                    <th style="width:20px;">Avg. Score</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $total = 0; $slNo = 1; ?>
                                                @foreach($scoresOfSubordinate as $scoreOfSubordinate)
                                                    <tr>
                                                        <td>{{$slNo++}}</td>
                                                        <td>{{$scoreOfSubordinate->Section}}</td>
                                                        <td>Goal Achievement</td>
                                                        <td>{{$scoreOfSubordinate->Score}}<?php $total+=$scoreOfSubordinate->Score; ?></td>
                                                    </tr>
                                                @endforeach
                                                <?php $average = $total/count($scoresOfSubordinate); ?>
                                                <tr>
                                                    <td colspan="3" style="text-align:right;font-weight:bold;">Average Weighted Score</td>
                                                    <td><strong>{{round($average,2)}}</strong></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-condensed" id="calc-total">
                                            <thead>
                                            <tr>
                                                <th style="width:40px;">Sl #</th>
                                                <th>Assessment Area</th>
                                                <th class="text-center" style="width:2%">Weight (%)</th>
                                                <th class="text-center" style="width:15%;">Self Rating</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count=1; ?>
                                            @foreach($assessmentAreas as $assessmentArea)
                                                <?php $randomKey = randomString(); ?>
                                                <tr>
                                                    <td class="text-center">{{$count}}.</td>
                                                    <td class="description">
                                                        <input type="hidden" name="pmssubmissiondetail[{{$randomKey}}][AssessmentArea]" value="{{$assessmentArea->Description}}"/>
                                                        <input type="hidden" name="pmssubmissiondetail[{{$randomKey}}][ApplicableToLevel2]" value="{{$assessmentArea->ApplicableToLevel2}}"/>
                                                        <input type="hidden" name="pmssubmissiondetail[{{$randomKey}}][DisplayOrder]" value="{{$assessmentArea->DisplayOrder}}"/>
                                                        {{$assessmentArea->Description}}

                                                    </td>
                                                    <td class="text-center">
                                                        <input type="hidden" name="pmssubmissiondetail[{{$randomKey}}][Weightage]" value="{{$assessmentArea->Weightage}}"/>
                                                        {{$assessmentArea->Weightage}}
                                                    </td>
                                                    <td @if(Auth::user()->PositionId <> CONST_POSITION_HOD && $count == 1 && Auth::user()->DepartmentId == 7)class="text-center"@endif>
                                                        <input type="number"
                                                                @if($count == 1)
                                                                    @if((bool)$subordinateScorePercentage)
                                                                        data-here="1" data-goalachievementscore="{{$goalAchievementScore}}" value="{{round((($subordinateScorePercentage/100 * $average) + ((100-$subordinateScorePercentage)/100 * $goalAchievementScore)),2)}}"
                                                                    @else
                                                                        data-here="2" value="{{$goalAchievementScore}}"
                                                                    @endif
                                                                @else
                                                                    @if($count == 1 && isset($average))
                                                                        value="{{round($average,2)}}"
                                                                    @endif
                                                                @endif
                                                                onkeydown="return event.keyCode !== 69" name="pmssubmissiondetail[{{$randomKey}}][SelfRating]" min="0" max="{{$assessmentArea->Weightage}}" step="any" @if($count == 1)id="score" @endif required="required" class="form-control input-xs figure"/>
                                                    </td>
                                                </tr>
                                                <?php $count++; ?>
                                            @endforeach
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Total</strong></td>
                                                <td>
                                                    <input type="text" autocomplete="off" class="form-control input-xs" id="figure-total" disabled="disabled"/>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" id="save-appraisee" class="btn btn-primary">Save as Draft</button>
                                    <button type="submit" class="btn btn-success">Submit</button>
                                    <a href="{{url('submitpms')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                                    {{Form::close()}}
                                @else
                                    @if($alreadySubmitted)
                                        <strong>You have already submitted PMS for this period.</strong>
                                    @endif
                                    @if($notWithinPMSPeriod)
                                        <strong>The last PMS Period is over.</strong>
                                    @endif
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@if(Auth::user()->PositionId <> CONST_POSITION_HOD && Auth::user()->DepartmentId == 7)
    @section('pagescripts')
    <script>
        $(document).ready(function(){
            setTimeout(function(){$("#score").click();},200);
        });
    </script>
    @endsection
@endif
