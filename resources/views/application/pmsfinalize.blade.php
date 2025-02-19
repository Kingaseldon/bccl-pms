@extends('master')
@section('page-title','Process PMS')
@section('page-header','Process PMS')
@section('pagestyles')
    {{Html::style("assets/plugins/lightcase/css/lightcase.css")}}
@endsection
@section('pagescripts')
    {{Html::script("assets/plugins/lightcase/js/lightcase.js")}}
@endsection
@section('content')
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
                                            <th style="width:50%;">Name <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Name}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:50%;">Extension <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Extension}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:50%;">Mobile No. <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->MobileNo}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:50%;">Email/Username <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Email}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:50%;">Department <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Department}}</td>
                                        </tr>
                                        @if((bool)$userDetail->Section)
                                        <tr>
                                            <th style="width:50%;">Section <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Section}}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th style="width:50%;">Designation <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->DesignationLocation}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:50%;">Basic Pay <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;">{{$userDetail->BasicPay}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:50%;">Grade/Step <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->GradeStep}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:50%;">PayScale <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->PayScale}}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                @if((bool)$application[0]->FilePath)
                                    <br>
                                    <a href="{{url('filedownload')}}?file={{$application[0]->FilePath}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Self Rated File</a>
                                    <br>
                                @endif
                                @if((bool)$application[0]->File3Path)
                                    <br>
                                    <a href="{{url('filedownload')}}?file={{$application[0]->File3Path}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 1 Rated File</a>
                                    <br>
                                @endif
                                @if((bool)$application[0]->File4Path)
                                    <br>
                                    <a href="{{url('filedownload')}}?file={{$application[0]->File4Path}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 2 Rated File</a>
                                    <br>
                                @endif
                                @foreach($pmsMultiple as $multiple)
                                    <br>
                                    <a href="{{url('filedownload')}}?file={{$multiple->FilePath}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download {{$multiple->ForLevel == 1 ? "Level 1":"Level 2"}} Rated File (Uploaded by {{$multiple->Appraiser}})</a>
                                    <br/>
                                @endforeach
                                @if((bool)$application[0]->File2Path)
                                    <br>
                                    <a href="{{url('filedownload')}}?file={{$application[0]->File2Path}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Supporting Document</a>
                                    <br><br>
                                @endif
                            </div>
                        </div>
                        {{Form::open(['url'=>'finalizepms'])}}
                        {{Form::hidden("Id",$application[0]->Id)}}
                            <br>
                            <h5>Rating</h5>
                            @include('includes.performancesummary')
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="PMSOutComeId" class="control-label">Action</label>
                                        <select name="PMSOutcomeId" id="PMSOutComeId" class="form-control select2">
                                            @foreach($outcomes as $outcome)
                                                <option value="{{$outcome->Id}}" data-reference="{{$outcome->ReferenceNo ?? '0'}}" data-designationlocationchange="{{$outcome->HasDesignationAndLocationChange}}" data-basicpaychange="{{$outcome->HasBasicPayChange}}" data-paychange="{{$outcome->HasPayChange}}" data-positionchange="{{$outcome->HasPositionChange}}" @if($outcome->Id == $application[0]->PMSOutcomeId)selected="selected"@endif>{{$outcome->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="OldBasicPay" class="control-label">Old Basic Pay</label>
                                        <input type="number" readonly="readonly" autocomplete="off" class="form-control" name="OldBasicPay" id="OldBasicPay" value="{{$details[0]->BasicPay}}" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="BasicPayScale" class="control-label">Basic Pay Scale</label>
                                        <input type="text" readonly="readonly" autocomplete="off" class="form-control" name="BasicPayScale" id="BasicPayScale" value="{{$details[0]->PayScale}}" />
                                    </div>
                                </div>
                            </div>
                            <?php
                                $basicPay = str_replace(",","",$userDetail->BasicPay);
                                $increment = $userDetail->Increment;
                            ?>
                            {{-- <?php $newBasic = ''; ?> --}}
                            {{-- @if($application[0]->PMSOutcomeId == CONST_PMSOUTCOME_DOUBLEINCREMENT) --}}
                            {{-- <?php $newBasic = doubleval($basicPay) + (2 * doubleval($increment)); ?> --}}
                            {{-- @endif --}}
                            {{-- @if($application[0]->PMSOutcomeId == CONST_PMSOUTCOME_SINGLEINCREMENT) --}}
                            {{-- <?php $newBasic = doubleval($basicPay) + doubleval($increment); ?> --}}
                            {{-- @endif --}}

                            <div class="row">
                                <div class="col-md-7 @if($application[0]->HasPositionChange != 1){{"hide"}}@endif" id="PositionWrapper">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="Grade" class="control-label">New Grade / Step</label>
                                                <select data-payscaletype="{{(int)$settingForPayScale}}" name="NewGradeStepId" id="Grade" class="form-control select2">
                                                    <option value="">N/A</option>
                                                    @if((int)$settingForPayScale === 1)
                                                        @foreach($gradesteps as $gradestep)
                                                            <option value="{{$gradestep->Id}}" data-basepay="{{$gradestep->StartingSalary ?? ''}}" data-lastpay="{{$gradestep->EndingSalary ?? ''}}" data-increment="{{$gradestep->Increment ?? ''}}" @if($application[0]->NewGradeStepId == $gradestep->Id)selected="selected"@endif>{{$gradestep->GradeStep}}</option>
                                                        @endforeach
                                                    @else
                                                        @foreach($gradesteps as $gradestep)
                                                            <option value="{{$gradestep->Id}}" data-basepay="{{$gradestep->StartingSalary ?? ''}}" data-middlepay="{{$gradestep->MiddleSalary ?? ''}}" data-lastpay="{{$gradestep->EndingSalary ?? ''}}" data-firstincrement="{{$gradestep->FirstIncrement ?? ''}}" data-secondincrement="{{$gradestep->SecondIncrement ?? ''}}" @if($application[0]->NewGradeStepId == $gradestep->Id)selected="selected"@endif>{{$gradestep->GradeStep}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="NewGradeId" class="control-label">Organization Role</label>
                                                <select name="NewGradeId" id="NewGradeId" class="form-control select2">
                                                    <option value="">N/A</option>
                                                    @foreach($grades as $grade)
                                                        <option value="{{$grade->Id}}" @if($grade->Id == ((bool)$application[0]->NewGradeId ? $application[0]->NewGradeId: (isset($employee['GradeId'])?$employee['GradeId']:old('GradeId')) ))selected="selected"@endif >{{$grade->Name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="Position" class="control-label">Reports to (Immediate)</label>
                                                <select name="NewSupervisorId" id="Position" class="form-control select2">
                                                    <option value="">N/A</option>
                                                    @foreach($supervisors as $supervisor)
                                                        <option value="{{$supervisor->Id}}" @if($supervisor->Id == ((bool)$application[0]->NewSupervisorId ? $application[0]->NewSupervisorId: (isset($employee['SupervisorId'])?$employee['SupervisorId']:old('SupervisorId')) ))selected="selected"@endif >{{$supervisor->Name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 @if($application[0]->HasBasicPayChange != 1){{"hide"}}@endif" id="PaySlabWrapper">
                                    <div class="form-group">
                                        <label for="PaySlab" class="control-label">Pay Slab</label>
                                        <select name="PaySlab" id="PaySlab" class="form-control select2 pay-slab-id">
                                            <option value="">-- SELECT ONE --</option>
                                            @foreach($payRaiseSlabs as $payRaiseSlab)
                                                <option value="{{$payRaiseSlab->Raise}}">{{$payRaiseSlab->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 @if($application[0]->HasPayChange != 1){{"hide"}}@endif" id="PayScaleWrapper">
                                    <div class="form-group">
                                        <label for="PayScale" class="control-label">New Pay Scale</label>
                                        <input type="text" autocomplete="off" value="{{$application[0]->NewPayScale}}" class="form-control" name="NewPayScale" id="PayScale"/>
                                    </div>
                                </div>
                                <div class="col-md-3 @if($application[0]->HasBasicPayChange != 1){{"hide"}}@endif" id="BasicPayWrapper">
                                    <div class="form-group">
                                        <label for="BasicPay" class="control-label">New Basic Pay</label>
                                        <input type="text" autocomplete="off" class="form-control" name="NewBasicPay" id="BasicPay"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row @if($application[0]->HasDesignationAndLocationChange != 1){{"hide"}}@endif" id="DesignationLocationWrapper">
                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Designation" class="control-label">New Designation</label>
                                                <select name="NewDesignationId" id="Designation" class="form-control select2">
                                                    <option value="">N/A</option>
                                                    @foreach($designations as $designation)
                                                        <option value="{{$designation->Id}}" @if($application[0]->NewDesignationId == $designation->Id)selected="selected"@endif>{{$designation->Name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="Remarks" class="control-label">Remarks</label>
                                <textarea class="form-control" name="FinalRemarks" id="Remarks">{{$application[0]->FinalRemarks}}</textarea>
                            </div>
                        <input type="hidden" id="old-basic-pay" value="{{str_replace(",","",$userDetail->BasicPay)}}"/>
                        <input type="hidden" id="old-payscale-increment" value="{{$userDetail->Increment}}"/>
                        {{--<button type="submit" name="Submit" value="1" class="btn btn-success dont-disable">Save as Draft</button>--}}
                        <button type="submit" name="Submit" value="2" class="btn btn-primary dont-disable">{{(bool)$application[0]->PMSOutcomeId?"Update":"Submit"}}</button>
                        <a href="{{url('appraisepms')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}

                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5>{{$userDetail->Name}}'s PMS History</h5>
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
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5>{{$userDetail->Name}}'s Disciplinary Record(s)</h5>
                                    <div class="table-responsive">
                                        <table class="table table-condensed table-bordered less-padding">
                                            <thead>
                                                <tr>
                                                    <th style="width:2px;">Sl#</th>
                                                    <th>Record Date</th>
                                                    <th>Record</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $count = 1; ?>
                                                @forelse($disciplinaryDetails as $disciplinaryDetail)
                                                    <tr>
                                                        <td>{{$count++}}</td>
                                                        <td>{{convertDateToClientFormat($disciplinaryDetail->RecordDate)}}</td>
                                                        <td>{{$disciplinaryDetail->Record}}</td>
                                                        <td>{{$disciplinaryDetail->RecordDescription}}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">No disciplinary records</td>
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
