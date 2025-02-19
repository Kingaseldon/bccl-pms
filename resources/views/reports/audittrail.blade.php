@extends('master')
@section('page-title','Audit Trail Report')
@section('page-header',"Audit Trail Report")

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 20px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{Request::url()}}" method="GET" id="form-daterestriction">
                            <div class="row">
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="User" class="control-label">User</label>
                                        <select name="UserId" class="form-control select2" id="User">
                                            <option value="">All</option>
                                            @foreach($adminUsers as $user)
                                                <option @if($user->Id == app('request')->input('UserId'))selected="selected"@endif value="{{$user->Id}}">{{$user->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label for="TableName" class="control-label">Table Name</label>
                                        <select name="TableName" class="form-control select2" id="TableName">
                                            <option value="">All</option>
                                            @foreach($tables as $table)
                                                <?php $tableName = ucfirst(substr($table->TableName,4,(strlen($table->TableName)-4))); ?>
                                                <option @if($tableName == app('request')->input('TableName'))selected="selected"@endif value="{{$tableName}}">{{$tableName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="FromDate" class="control-label">From Date</label>
                                        <input type="date" autocomplete="off" name="FromDate" value="{{app('request')->input('FromDate')}}" id="FromDate" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="ToDate" class="control-label">To Date</label>
                                        <input @if(app('request')->has('FromDate'))min="{{app('request')->input('FromDate')}}"@endif type="date" autocomplete="off" name="ToDate" value="{{app('request')->input('ToDate')}}" id="ToDate" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="Deleted" class="control-label">Type</label>
                                        {{Form::select("Deleted",[''=>'All','1'=>'Deleted Record','0'=>'New / Updated Record'],app('request')->input('Deleted'),['class'=>"form-control select2"])}}
                                    </div>
                                </div>
                                {{--<div class="col-md-6 col-lg-3">--}}
                                    {{--<div class="form-group">--}}
                                        {{--<label for="PMSPeriod" class="control-label">PMS Period</label>--}}
                                        {{--<select name="PMSPeriod[]" class="form-control select2 select2multiple" id="PMSPeriod" multiple>--}}
                                            {{--@foreach($pmsPeriods as $pmsPeriod)--}}
                                                {{--<option @if(in_array($pmsPeriod->Id,empty(app('request')->input('PMSPeriod'))?[]:app('request')->input('PMSPeriod')))selected="selected"@endif value="{{$pmsPeriod->Id}}">{{date_format(date_create($pmsPeriod->StartDate),"M, Y")}}</option>--}}
                                            {{--@endforeach--}}
                                        {{--</select>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--<div class="col-md-6 col-lg-3">--}}
                                    {{--<div class="form-group">--}}
                                        {{--<label for="DesignationLocation" class="control-label">Designation/Location</label>--}}
                                        {{--<select name="DesignationId" class="form-control select2" id="DesignationLocation">--}}
                                            {{--<option value="">All</option>--}}
                                            {{--@foreach($designationLocations as $designationLocation)--}}
                                                {{--<option data-deptids='[{{$designationLocation->DepartmentIds}}]' @if($designationLocation->Id == app('request')->input('DesignationId'))selected="selected"@endif value="{{$designationLocation->Id}}">{{$designationLocation->Name}}</option>--}}
                                            {{--@endforeach--}}
                                        {{--</select>--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                {{--<div class="col-md-6 col-lg-2">--}}
                                    {{--<div class="form-group">--}}
                                        {{--<label for="RoleId" class="control-label">Role</label>--}}
                                        {{--<select name="RoleId" class="form-control select2" id="RoleId">--}}
                                            {{--<option value="">All</option>--}}
                                            {{--@foreach($roles as $role)--}}
                                                {{--<option @if($role->Id == app('request')->input('RoleId'))selected="selected"@endif value="{{$role->Id}}">{{$role->Name}}</option>--}}
                                            {{--@endforeach--}}
                                        {{--</select>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                <input type="hidden" value="1" name="Submitted"/>
                            </div>
                            <div class="col-lg-5 col-md-5 col-sm-5 col-8">
                                <div class="row">
                                    <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                    <a href="{{url('audittrailreport')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a> &nbsp;
                                    {{--<button id="download-xlsx" type="button" class="btn btn-success"><i class="fa fa-file-excel-o"></i> &nbsp;Export to Excel</button>--}}
                                </div>
                            </div>
                        </form>
                        <div class="row">

                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered">
                                    {{--<table class="table table-condensed table-bordered">--}}
                                        <thead>
                                            <tr>
                                                <th>Sl #</th>
                                                <th>Table Name</th>
                                                <th>Changed By</th>
                                                <th>Changed On</th>
                                                <th>Is Deleted</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                            @forelse($reportData as $data)
                                                <tr>
                                                    <td>{{$slNo++}}</td>
                                                    <td style="text-transform:capitalize;">{{substr($data->TableName,4,(strlen($data->TableName) - 4))}}</td>
                                                    <td>{{$data->Name}}</td>
                                                    <td>{{$data->ChangedOn}}</td>
                                                    <td>{{$data->Deleted}}</td>
                                                    <td><?php $changes = $data->Changes; $decoded = json_decode($changes,true); ?>
                                                        @foreach($decoded as $key=>$value)
                                                            @foreach($value as $a=>$b)
                                                                {{$a}} : {{$b}} <br/>
                                                            @endforeach
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No data to display!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{$reportData->appends(app('request')->except('page'))->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php //dd(count($dataArraySuper)); ?>
@endsection

