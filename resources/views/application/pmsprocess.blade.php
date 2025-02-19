@extends('master')
@section('page-title','Appraise PMS')
@section('page-header','Appraise PMS')
@section('pagestyles')
    {{Html::style("assets/plugins/lightcase/css/lightcase.css")}}
@endsection
@section('pagescripts')
    {{Html::script("assets/plugins/lightcase/js/lightcase.js")}}
@endsection
@section('content')
    <div class="modal" id="subordinate-pg-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="subordinate-pg-form">

            </div>
        </div>
    </div>
    <div class="row m-b-30 dashboard-header" id="disable-return">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-9 card dashboard-product">
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
                        <h5>Personal Information</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <table style="width:100%;">
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th colspan="2" class="text-center">
                                                @if((bool)$userDetail->ProfilePicPath)
                                                    <a href="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" data-rel="lightcase">
                                                @endif
                                                    <img class="rounded-circle img-thumbnail" src="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" style="height:117px;"/>
                                                @if((bool)$userDetail->ProfilePicPath)
                                                    </a>
                                                @endif
                                            </th>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Name <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Name}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Extension <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Extension ?? 'N/A'}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Mobile No. <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->MobileNo}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Email/Username <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Email}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Department <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Department}}</td>
                                        </tr>
                                        @if((bool)$userDetail->Section)
                                        <tr>
                                            <th style="width:35%;">Section <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Section}}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th style="width:35%;">Grade/Step <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->GradeStep}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Designation <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->DesignationLocation}}</td>
                                        </tr>
                                        @if((bool)$userDetail->Qualification1 || (bool)$userDetail->Qualification2)
                                            <tr>
                                                <th style="width:35%;">Qualification<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> @if((bool)$userDetail->Qualification1){{$userDetail->Qualification1}}@endif @if((bool)$userDetail->Qualification2)@if((bool)$userDetail->Qualification1)<br/>@endif{{$userDetail->Qualification2}}@endif </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        @if(($hasMultipleLevel1 && $loggedInLevel == 1) || ($hasMultipleLevel2 && $loggedInLevel == 2))
                            <?php $url = 'processpmsmultiple'; ?>
                        @else
                            <?php $url = 'processpms'; ?>
                        @endif
                        {{Form::open(['url'=>$url,'files'=>true,'id'=>'process-form'])}}
                        {{Form::hidden('Type',$type)}}
                        {{Form::hidden('Id',$id)}}
                        {{Form::hidden('Level',($type==1 || $hasNoLevel2)?'1':'2')}}
                            <div class="row">
                                <div class="col-md-4">
                                    @if((bool)$pmsFile)
                                        <br>
                                        <a href="{{url('filedownload')}}?file={{$pmsFile}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Self Rated File</a>
                                        <br>
                                    @endif
                                    @if((bool)$filePath3)
                                        <br>
                                        <a href="{{url('filedownload')}}?file={{$filePath3}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 1 Rated File</a>
                                        <br>
                                    @endif
                                    @if((bool)$filePath4)
                                        <br>
                                        <a href="{{url('filedownload')}}?file={{$filePath4}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 2 Rated File</a>
                                        <br>
                                    @endif
                                    <?php $multipleAppraiserRemark = ''; ?>
                                    @foreach($pmsMultiple as $multiple)
                                        @if($multiple->AppraisedByEmployeeId == Auth::user()->Id)
                                            <?php $multipleAppraiserRemark = $multiple->Remarks; ?>
                                        @endif
                                        @if((bool)$multiple->FilePath)
                                            <br>
                                            <a href="{{url('filedownload')}}?file={{$multiple->FilePath}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download {{$multiple->ForLevel == 1 ? "Level 1":"Level 2"}} Rated File (Uploaded by {{$multiple->Appraiser}})</a>
                                            <br/>
                                        @endif
                                    @endforeach
                                    @if((bool)$filePath2)
                                        <br>
                                        <a href="{{url('filedownload')}}?file={{$filePath2}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Supporting Document</a>
                                        <br><br>
                                    @endif

                                </div>
                            </div>
                            @if($type == 1 || $hasNoLevel2)
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ExcelApplicant">Goals/Targets {{(bool)$pmsFile?"Re-upload":"Upload"}} file (optional) [5MB Max]</label>
                                            <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="ExcelApplicant2" name="@if((bool)$filePath3 && $type == 2 && !$hasNoLevel2){{"File4"}}@else{{"File3"}}@endif" autocomplete="off" class="form-control file-xs"/>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <br>
                            @endif
                        <div class="row">
                            <div class="col-md-4">
                                <?php $multipleAppraiserRemark = ''; ?>
                                @foreach($pmsMultiple as $multiple)
                                    @if($multiple->AppraisedByEmployeeId == Auth::user()->Id)
                                        <?php $multipleAppraiserRemark = $multiple->Remarks; ?>
                                    @endif
                                @endforeach

                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed less-padding" id="calc-total">
                                <thead>
                                <tr>
                                    <th>Sl #</th>
                                    <th>Assessment Area</th>
                                    <th class="text-center">Weight (%)</th>
                                    <th class="text-center" style="width:16%;">Self Rating</th>
                                    @if($type == 2 && !$hasNoLevel2)
                                        <th class="text-center" style="width:16%;">Rating by @if(strlen($empDetails[0]->Level1Employee)>30)<br/>@endif{!! $empDetails[0]->Level1Employee !!}</th>
                                    @endif
                                    <th class="text-center" style="width:16%;">Your Score</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $count=1; $selfRatingTotal = $level1RatingTotal = $level2RatingTotal = 0; ?>
                                @foreach($pmsDetails as $assessmentArea)
                                    <?php $randomKey = randomString().randomString(); ?>
                                    <tr>
                                        <td>{{$count}}.</td>
                                        <td class="description">
                                            @if($type == 1 || $empDetails[0]->Level2CriteriaType == 2 || $assessmentArea->ApplicableToLevel2 == 1 || $hasNoLevel2)
                                                <input type="hidden" name="pmssubmissiondetail[{{$randomKey}}][Id]" value="{{$assessmentArea->Id}}"/>
                                            @endif
                                            {{$assessmentArea->AssessmentArea}}
                                        </td>
                                        <td class="text-center">
                                            {{$assessmentArea->Weightage}}
                                        </td>
                                        <td class="text-center">
                                            {{$assessmentArea->SelfRating}} <?php $selfRatingTotal += $assessmentArea->SelfRating; ?>
                                        </td>
                                        @if($type == 2 && !$hasNoLevel2)
                                            <td class="text-center">
                                                @if(Auth::user()->DepartmentId == 7 && $count == 1)
                                                   &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$assessmentArea->Level1Rating}}&nbsp;
                                                    <button type="button" class="btn btn-primary btn-xs" id="subordinate-performance-goal-l2">
                                                        <i class="fa fa-eye" style="color:white;cursor:pointer;"></i>
                                                    </button>
                                                @else
                                                    {{$assessmentArea->Level1Rating}}
                                                @endif
                                            </td>
                                        @endif
                                        <?php $level1RatingTotal += $assessmentArea->Level1Rating; ?>

                                        <td @if($count == 1)class="text-center"@endif>
                                            @if($type == 1 || $empDetails[0]->Level2CriteriaType == 2 || $assessmentArea->ApplicableToLevel2 == 1 || $hasNoLevel2)
                                                @if(count($multipleDetailArray) > 0)
                                                    <input type="number" onkeydown="return event.keyCode !== 69" value="{{$multipleDetailArray[$assessmentArea->Id]}}<?php $level2RatingTotal += $multipleDetailArray[$assessmentArea->Id]; ?>" name="pmssubmissiondetail[{{$randomKey}}][{{($type==1 || $hasNoLevel2) ? "Level1Rating":"Level2Rating"}}]" min="0" max="{{$assessmentArea->Weightage}}" step="any" required="required" class="form-control input-xs figure"/>
                                                @else
                                                    @if($count == 1)
                                                        <div class="row mr-0 ml-0">
                                                            <div class="col-9 pl-0 text-center">
                                                                <input type="text" readonly="readonly" style="width:100%;" onkeydown="return event.keyCode !== 69" value="{{$goalAchievementScore}}" min="0" max="{{$assessmentArea->Weightage}}" step="any" required="required" class="form-control text-center input-xs figure" name="pmssubmissiondetail[{{$randomKey}}][{{($type==1 || $hasNoLevel2) ? "Level1Rating":"Level2Rating"}}]" />
                                                            </div>
                                                            <div class="col-3 pl-0 text-left">
                                                                <button type="button" class="btn btn-primary btn-xs" id="subordinate-performance-goal">
                                                                    <i class="fa fa-eye" style="color:white;cursor:pointer;"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <input type="number" onkeydown="return event.keyCode !== 69" value="@if(in_array($empDetails[0]->LastStatusId,[CONST_PMSSTATUS_DRAFT])){{(($type==1 || $hasNoLevel2) && (bool)$assessmentArea->Level1Rating) ? $assessmentArea->Level1Rating:((bool)$assessmentArea->Level2Rating ? $assessmentArea->Level2Rating:'')}}<?php if(!($type==1 && (bool)$assessmentArea->Level1Rating) && (bool)$assessmentArea->Level2Rating): ?><?php $level2RatingTotal += $assessmentArea->Level2Rating; ?><?php endif; ?>@endif" name="pmssubmissiondetail[{{$randomKey}}][{{($type==1 || $hasNoLevel2) ? "Level1Rating":"Level2Rating"}}]" min="0" max="{{$assessmentArea->Weightage}}" step="any" required="required" class="form-control input-xs figure"/>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    <?php $count++; ?>
                                @endforeach
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                                    <td class="text-center">
                                        {{number_format($selfRatingTotal,2)}}
                                    </td>
                                    @if($type == 2 && !$hasNoLevel2)
                                        <td class="text-center">
                                            {{number_format($level1RatingTotal,2)}}
                                        </td>
                                    @endif
                                    <td>
                                        <input type="text" autocomplete="off" @if(($type == 1 || $hasNoLevel2) && $level1RatingTotal > 0 && in_array($empDetails[0]->LastStatusId,[CONST_PMSSTATUS_DRAFT]))value="{{number_format($level1RatingTotal,2)}}"@else value="{{number_format($level2RatingTotal,2)}}" @endif class="form-control input-xs" id="figure-total" disabled="disabled"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <!--HISTORY OF APPLICATION -->
                        <div class="well">
                            <h6>History of Evaluation</h6>
                            {!! $empDetails[0]->History !!}
                        </div>
                        <div class="form-group">
                            <label for="Remarks" class="control-label">Remarks</label>
                            <textarea class="form-control" name="Remarks" id="Remarks">{{$pmsDetails[0]->StatusByEmployeeId == Auth::user()->Id ? $pmsDetails[0]->Remarks:(count($pmsMultiple)>0 ? $multipleAppraiserRemark:'')}}</textarea>
                        </div>

                        @if(($hasMultipleLevel1 && $loggedInLevel == 1) || ($hasMultipleLevel2 && $loggedInLevel == 2))
                            <button type="submit" name="SaveType" value="2" class="dont-disable remove-required btn btn-primary">Save as Draft</button>
                            <button type="submit" name="SaveType" id="submit-process" value="1" class="dont-disable btn btn-success">Submit</button>
                            <a href="{{url('sendback',[$id])}}" id="send-back" class="btn btn-warning">Send back</a>
                            <a href="{{url('appraisepms')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        @else
                            <button type="button" name="SaveType" value="2" id="save-appraiser" class="btn btn-primary">Save as Draft</button>
                            <button type="submit" name="SaveType" value="1" id="submit-process" class="btn btn-success">Submit</button>
                            <a href="{{url('sendback',[$id])}}" id="send-back" class="btn btn-warning">Send back</a>
                            <a href="{{url('appraisepms')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        @endif

                        {{Form::close()}}
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>{{$userDetail->Name}}'s PMS History</h5>
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered less-padding">
                                        <thead>
                                        <tr>
                                            <th style="width:40px;">Sl#</th>
                                            <th>PMS Period</th>
                                            <th>Score</th>
                                            <th>Result</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $slNo = 1; ?>
                                        @forelse($history as $data)
                                            <?php
                                            $month = date_format(date_create($data->StartDate),'m');
                                            $year = date_format(date_create($data->StartDate),'Y');
                                            if($month == '01'){
                                                $period = "1st July, ".($year - 1)." - 31st December, ".($year - 1);
                                            }else{
                                                $period = "1st Jan, ".$year." - 30th June, ".$year;
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center">{{$slNo++}}</td>
                                                <td>PMS {{$data->PMSNumber}};  {{$period}}</td>
                                                <td>{{$data->PMSScore}}</td>
                                                <td>{{$data->PMSResult}} @if((bool)$data->PMSRemarks)<br/>{{$data->PMSRemarks}}@endif</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4"><center>No PMS done till date.</center></td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
