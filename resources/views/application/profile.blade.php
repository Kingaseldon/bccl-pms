@extends('master')
@section('page-title','Your Profile')
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
                                <h6>Your Profile</h6>
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
                                            </th>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-center">
                                                @if(!$hasParam)
                                                    <a href="#profilePicModal" data-toggle="modal" class="btn btn-xs btn-inverse-primary"><i class="fa fa-edit"></i> {{(bool)$userDetail->ProfilePicPath?"Change":'Upload'}} Profile Picture</a>
                                                @endif
                                                <br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Name <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Name}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Date of Birth  <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{(bool)$userDetail->DateOfBirth?convertDateToClientFormat($userDetail->DateOfBirth):'N/A'}}</td>
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
                                            <td style="padding-left:25px;"> {{$userDetail->CIDNo ?? 'N/A'}}</td>
                                        </tr>
                                        @if((bool)$userDetail->Qualification1 || (bool)$userDetail->Qualification2)
                                        <tr>
                                            <th style="width:35%;">Qualification<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> @if((bool)$userDetail->Qualification1){{$userDetail->Qualification1}}@endif @if((bool)$userDetail->Qualification2)@if((bool)$userDetail->Qualification1)<br/>@endif{{$userDetail->Qualification2}}@endif </td>
                                        </tr>
                                        @endif
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
                                            <td style="padding-left:25px;"> {{(bool)$userDetail->DateOfAppointment?convertDateToClientFormat($userDetail->DateOfAppointment):'N/A'}}</td>
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
                                            <th style="width:35%;">Grade<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
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
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th style="width:35%;">Status <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            @if($hasParam)
                                                <td style="padding-left:25px;"> Regular</td>
                                            @else
                                                <td style="padding-left:25px;"> Regular</td>
                                            @endif
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
    <div class="modal" id="profilePicModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{(bool)$userDetail->ProfilePicPath?"Change":'Upload'}} Profile Picture</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                {{Form::open(['url'=>'saveprofilepic','files'=>true])}}
                <div class="modal-body">
                    {{Form::hidden('Id',Auth::user()->Id)}}
                    <div class="form-group">
                        <label for="Image" style="color:#000;">Image <span class="required">*</span></label>
                        <input type="file" accept=".PNG,.JPG,.JPEG,.GIF,.png,.jpg,.jpeg,.gif" id="Image" required="required" name="Image" autocomplete="off" class="form-control"/>
                        <span><strong>Use a square image (similar width and height) for best display.</strong></span><br>
                        <span><strong>File size cannot be more than 5MB.</strong></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
                {{Form::close()}}
            </div>
        </div>
    </div>
@endsection
