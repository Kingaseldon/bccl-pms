@extends('master')
@section('page-title','Employee Profile')
@section('pagestyles')
    {{Html::style("assets/plugins/lightcase/css/lightcase.css")}}
@endsection
@section('pagescripts')
    {{Html::script("assets/plugins/lightcase/js/lightcase.js")}}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-6">
                    <div class="col-sm-12 card" style="padding-top: 16px;padding-bottom: 18px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Employee Profile</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table style="width:100%;" class="simple-table">
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th colspan="2" class="text-center">
                                                @if((bool)$userDetail->ProfilePicPath)
                                                    <a href="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" data-rel="lightcase">
                                                @endif
                                                    <img class="rounded-circle img-thumbnail" src="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" style="height:117px; "/>
                                                @if((bool)$userDetail->ProfilePicPath)
                                                    </a>
                                                @endif
                                                <br><br>
                                            </th>
                                        </tr>
                                        {{--<tr>--}}
                                            {{--<td colspan="2" class="text-center">--}}
                                                {{--<a href="#profilePicModal" data-toggle="modal" style="color:blue;" class="btn btn-xs btn-inverse-primary"><i class="fa fa-edit"></i> {{(bool)$userDetail->ProfilePicPath?"Change":'Upload'}} Profile Picture</a>--}}
                                                {{--<br><br>--}}
                                            {{--</td>--}}
                                        {{--</tr>--}}
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
                                            <th style="width:35%;">CID <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
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
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th style="width:35%;">Date of Appointment  <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{convertDateToClientFormat($userDetail->DateOfAppointment)}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Employment Type<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> Regular</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Designation <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->DesignationLocation}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Grade/Step<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->GradeStep}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Current Pay Scale<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->PayScale}}</td>
                                        </tr>
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
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th style="width:35%;">Status <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->GradeId==CONST_GRADE_E0?'Contract':$statusArray[$userDetail->Status]}}</td>
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
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        {{--                                        <a href="{{url('sectioninput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h6>{{$details[0]->Name}}'s PMS History</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered less-padding">
                                        <thead>
                                        <tr>
                                            <th>Sl#</th>
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
                                                <td>{{$slNo++}}</td>
                                                <td>PMS {{$data->PMSNumber}};  {{$period}}</td>
                                                <td>{{$data->PMSScore}}</td>
                                                <td>{{$data->PMSResult}} @if((bool)$data->PMSRemarks)<br/>{{$data->PMSRemarks}}@endif</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4"><center>No PMS done till date.</center></td>
                                            </tr>
                                        @endforelse

                                        {{--<tr>--}}
                                        {{--<td>2</td>--}}
                                        {{--<td>PMS 16 1st July, 2017 - 31st December, 2017</td>--}}
                                        {{--<td>90</td>--}}
                                        {{--<td> -- </td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                        {{--<td>3</td>--}}
                                        {{--<td>PMS 15 1st Jan, 2017 - 30th June, 2017</td>--}}
                                        {{--<td>95</td>--}}
                                        {{--<td>Single Promotion</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                        {{--<td>4</td>--}}
                                        {{--<td>PMS 14 1st July, 2016 - 31st December, 2016</td>--}}
                                        {{--<td>95</td>--}}
                                        {{--<td>-</td>--}}
                                        {{--</tr>--}}
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
@endsection
