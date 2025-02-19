@extends('master')
@section('page-title',(bool)$type?'PMS Results':'My Current PMS Results')
@section('page-header',(bool)$type?'PMS Results':'My Current PMS Results')
@section('pagestyles')
    {{Html::style("assets/plugins/lightcase/css/lightcase.css")}}
@endsection
@section('pagescripts')
    {{Html::script("assets/plugins/lightcase/js/lightcase.js")}}
@endsection
@section('content')
    @if((bool)$type)
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="col-sm-12 card" style="padding-top: 16px;padding-bottom: 18px;">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Your Profile</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table style="width:100%;" class="simple-table">
                                        @foreach($profileDetails as $userDetail)
                                            <tr>
                                                <th colspan="2" class="text-center">
                                                    @if((bool)$userDetail->ProfilePicPath)
                                                        <a href="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" data-rel="lightcase">
                                                    @endif
                                                        <img class="rounded-circle img-thumbnail" src="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" style="height:117px; "/>
                                                    @if((bool)$userDetail->ProfilePicPath)
                                                        </a>
                                                    @endif
                                                </th>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-center">
                                                    <br>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Name <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->Name}}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Date of Birth  <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{convertDateToClientFormat($userDetail->DateOfBirth)}}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Mobile <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->MobileNo}}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Extension <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->Extension}}</td>
                                            </tr>
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Email/Username <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->Email}}</td>--}}
                                            {{--</tr>--}}
                                            <tr>
                                                <th style="width:35%;">Emp Id <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->EmpId}}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">CID<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->CIDNo}}</td>
                                            </tr>
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Position <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->Position}}</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Employment Type<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> Regular</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Date of Appointment  <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{date('Y-m-d')}}</td>--}}
                                            {{--</tr>--}}

                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Email/Username <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->Email}}</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Section <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->Section}}</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Department <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->Department}}</td>--}}
                                            {{--</tr>--}}

                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Designation <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->DesignationLocation}}</td>--}}
                                            {{--</tr>--}}
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="col-lg-6">
                        <div class="col-sm-12 card" style="padding-top: 16px;padding-bottom: 18px;">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Employment Details</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table style="width:100%;" class="simple-table">
                                        @foreach($profileDetails as $userDetail)
                                            <tr>
                                                <th style="width:35%;">Date of Appointment  <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{convertDateToClientFormat($userDetail->DateOfAppointment)}}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Duration of Service <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;">
                                                    <?php
                                                    $dateOfRegularization = new DateTime($userDetail->DateOfRegularization);
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
                                                </td>
                                            </tr>
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Employment Type<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> Regular</td>--}}
                                            {{--</tr>--}}
                                            <tr>
                                                <th style="width:35%;">Designation <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->DesignationLocation}}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Grade/Step<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->GradeStep ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Current Pay Scale<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->PayScale ?? 'N/A'}}</td>
                                            </tr>
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Last Promotion <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> 2016-02-12</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Last Increment <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> 2018-08-04</td>--}}
                                            {{--</tr>--}}
                                            <tr>
                                                <th style="width:35%;">Appraiser (Level 1) <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {!! $userDetail->Level1Name ?? 'N/A' !!}</td>
                                            </tr>
                                            <tr>
                                                <th style="width:35%;">Appraiser (Level 2) <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {!! $userDetail->Level2Name ?? 'N/A' !!}</td>
                                            </tr>
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Section <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->Section}}</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                            {{--<th style="width:35%;">Department <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                            {{--<td style="padding-left:25px;"> {{$userDetail->Department}}</td>--}}
                                            {{--</tr>--}}


                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 card" style="padding-top: 16px;padding-bottom: 18px;">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Employment Status</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table style="width:100%;" class="simple-table">
                                        <?php $statusArray = ['3'=>'Probation','4'=>'Suspended','5'=>'Terminated','1'=>'Regular','2'=>'In-active (EOL, Study Leave, etc)','0'=>'Resigned']; ?>
                                        @foreach($profileDetails as $userDetail)
                                            <tr>
                                                <th style="width:35%;">Status <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                                <td style="padding-left:25px;"> {{$userDetail->GradeStepId==CONST_GRADE_E0?'Contract':$statusArray[$userDetail->Status]}}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-12 card dashboard-product">
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
                        {{--<h5>Rating</h5>--}}

                            @include('includes.performancesummary')
                            <div class="well">
                                <h6>History of Evaluation</h6>
                                {!! $submissionHistoryWithRemarks !!}
                            </div>
                            @if((bool)$type && $type == 2)
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
                                            @forelse($pmsHistory as $data)
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
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

