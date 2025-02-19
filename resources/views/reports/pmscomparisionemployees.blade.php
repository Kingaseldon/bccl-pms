@extends('master')
@if(Request::segment(1)!='pmscomparisionemployeesiframe')
@section('page-title','PMS Comparision')
@section('page-header',"PMS Comparision")
@endif

@section('content')
    @if(Request::segment(1)!='pmscomparisionemployeesiframe')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
    @endif
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{Request::url()}}" method="GET">
                            <div class="row">
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="FilterType" class="control-label">Compare by</label>
                                        {{Form::select("FilterType",['4'=>'Employee','1'=>'Grade/Step','2'=>'Designation','3'=>'Appointment Date'],app('request')->input('FilterType'),['class'=>'radio-toggle form-control select2','id'=>'FilterType'])}}
                                    </div>
                                </div>
                                <?php $filterType = app('request')->input('FilterType',4) ?>
                                <div class="col-md-6 col-lg-3 toggle-filter @if(!(app('request')->input('FilterType')==4 || !app('request')->has('FilterType'))){{"hide"}}@endif" data-filtertype="4">
                                    <div class="form-group">
                                        <label for="EmployeeId" class="control-label">Employee</label>
                                        <select name="EmployeeId[]" multiple class="radio-toggle form-control select2multiple" id="EmployeeId" @if(app('request')->input('FilterType')!=4 && app('request')->has('FilterType'))disabled="disabled"@endif>
                                            @foreach($employeeList as $singleEmployee)
                                                <option @if(in_array($singleEmployee->Id,empty(app('request')->input('EmployeeId'))?[]:app('request')->input('EmployeeId')))selected="selected"@endif value="{{$singleEmployee->Id}}">{{$singleEmployee->Name}} [{{$singleEmployee->Designation}}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2 toggle-filter @if(!(app('request')->input('FilterType')==1)){{"hide"}}@endif" data-filtertype="1">
                                    <div class="form-group">
                                        <label for="GradeStep" class="control-label">Grade/Step</label>
                                        <select name="GradeStep" class="radio-toggle form-control select2" id="GradeStep" @if(app('request')->input('FilterType')!=1)disabled="disabled"@endif>
                                            <option value="">All</option>
                                            @foreach($gradeSteps as $gradeStep)
                                                <option data-y="{{app('request')->input('GradeStep')}}" @if($gradeStep->Id == app('request')->input('GradeStep'))selected="selected"@endif value="{{$gradeStep->Id}}">{{$gradeStep->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 toggle-filter @if(!(app('request')->input('FilterType')==2)){{"hide"}}@endif" data-filtertype="2">
                                    <div class="form-group">
                                        <label for="Designation" class="control-label">Designation</label>
                                        <select name="Designation" class="radio-toggle form-control select2" id="Designation" @if(app('request')->input('FilterType')!=2)disabled="disabled"@endif>
                                            <option value="">All</option>
                                            @foreach($designations as $designation)
                                                <option @if($designation->Id == app('request')->input('Designation'))selected="selected"@endif value="{{$designation->Id}}">{{$designation->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 toggle-filter @if(!(app('request')->input('FilterType')==3)){{"hide"}}@endif" data-filtertype="3">
                                    <div class="form-group">
                                        <label for="AppointmentDate" class="control-label">Appointment Date</label>
                                        <select name="AppointmentDate" class="radio-toggle form-control select2" id="AppointmentDate" @if(app('request')->input('FilterType')!=3)disabled="disabled"@endif>
                                            <option value="">All</option>
                                            @foreach($appointmentDates as $appointmentDate)
                                                <option @if($appointmentDate->DateOfAppointment == app('request')->input('AppointmentDate'))selected="selected"@endif value="{{$appointmentDate->DateOfAppointment}}">{{convertDateToClientFormat($appointmentDate->DateOfAppointment)}} [{{$appointmentDate->Number}} Employee(s)]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label for="PMSPeriod" class="control-label">PMS Period</label>
                                        <select name="PMSPeriod[]" class="form-control select2 select2multiple" id="PMSPeriod" multiple>
                                            @foreach($pmsPeriods as $pmsPeriod)
                                                <option @if(in_array($pmsPeriod->Id,empty(app('request')->input('PMSPeriod'))?[]:app('request')->input('PMSPeriod')))selected="selected"@endif value="{{$pmsPeriod->Id}}">{{date_format(date_create($pmsPeriod->StartDate),"M, Y")}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
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
                                        {{--<label for="Name" class="control-label">Name</label>--}}
                                        {{--<input type="text" autocomplete="off" name="Name" value="{{app('request')->input('Name')}}" id="Name" class="form-control"/>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--<div class="col-md-6 col-lg-2">--}}
                                    {{--<div class="form-group">--}}
                                        {{--<label for="PABX" class="control-label">IP Extension</label>--}}
                                        {{--<input type="text" autocomplete="off" name="PABX" value="{{app('request')->input('PABX')}}" id="PABX" class="form-control"/>--}}
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
                                    <a href="{{Request::url()}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a> &nbsp;
                                    @if(count($employees)>0)<button id="download-xlsx" type="button" class="btn btn-inverse-success"><i class="fa fa-file-excel-o"></i> &nbsp;Export to Excel</button>@endif
                                </div>
                            </div>
                        </form>
                        <div class="row">

                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                    <table id="tabulator-apply" class="hide table table-condensed table-bordered">
                                    {{--<table class="table table-condensed table-bordered">--}}
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                @if($filterType != 2)
                                                    {{--<th>Designation</th>--}}
                                                @endif
                                                <th>Work Location</th>
                                                @if($filterType != 1)
                                                    <th>Grade</th>
                                                @endif
                                                @if($filterType != 3)
                                                    <th>DoA</th>
                                                @endif
                                                <th>Duration of Service</th>
                                                <th>Basic Pay</th>
                                                {{--<th>Emp Id</th>--}}
                                                {{--<th>CID</th>--}}
                                            <?php $labelArray = []; $dataArray = []; $totalPMSPeriods = 0; ?>
                                                @foreach($pmsPeriods as $pmsPeriod)
                                                    @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                                                        <?php $totalPMSPeriods += 1; ?>
                                                        <?php array_push($labelArray,date_format(date_create($pmsPeriod->StartDate),"M, Y")); ?>
                                                        <th>{{date_format(date_create($pmsPeriod->StartDate),"M, Y")}}</th>
                                                        <th><div style="display:none;">{{date_format(date_create($pmsPeriod->StartDate),"M, Y")}}</div> - Result</th>
                                                        <th>{{date_format(date_create($pmsPeriod->StartDate),"M, Y")}} - Remarks</th>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $slNo = 1; $dataArraySuper = []; $employeeNames = []; $totalRows = count($employees); ?>
                                            @forelse($employees as $employee)
                                                <?php array_push($employeeNames,$employee->Name); ?>
                                                <tr style="border-bottom:2px solid black;">
                                                    <td>{{$employee->Name}} ({{$employee->Dept}})</td>
                                                    @if($filterType != 2)
{{--                                                        <td>{{$employee->Designation}}</td>--}}
                                                    @endif
                                                    <td><?php $variable = "Work Location"; ?>{{$employee->$variable}}</td>
                                                    @if($filterType != 1)
                                                        <td>{{(string)$employee->Grade}}</td>
                                                    @endif
                                                    @if($filterType != 3)
                                                        <td>{{convertDateToClientFormat($employee->DoA)}}</td>
                                                    @endif




                                                    <td>
{{--                                                        {{$employee->DateOfRegularization}}--}}
                                                        <?php
                                                            $dateOfRegularization = new DateTime($employee->DateOfRegularization);
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
                                                    <td><?php $variable = 'Basic Pay'; ?>{{(string)$employee->$variable}}</td>
                                                    {{--<td>{{(string)$employee->EmpId}}</td>--}}
                                                    {{--<td>{{(string)$employee->CIDNo}}</td>--}}
                                                    <?php $dataArraySub = []; $dataArray = [] ?>
                                                    @foreach($pmsPeriodArray as $pmsId)
                                                        @forelse($pmsResultData[$employee->Id][$pmsId] as $data)
                                                            <td>{{$data->PMSScore}}</td>
                                                            <?php array_push($dataArray,$data->PMSScore); ?>
                                                            <td>{{$data->PMSResult}}</td>
                                                            <td>{{$data->PMSRemarks}}</td>
                                                        @empty
                                                            <?php array_push($dataArray,0); ?>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        @endforelse

                                                    @endforeach
                                                    <?php array_push($dataArraySub,$dataArray); ?>
                                                </tr>
                                                <?php array_push($dataArraySuper,$dataArraySub); ?>
                                            @empty

                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{--{{$employees->appends(app('request')->except('page'))->links()}}--}}
                                </div>
                            </div>

                                <div id="canvas-container" class="row" style="text-align:center;width:100%;">
                                    <div id="canvas-inner" style="overflow-x:scroll; width:1030px;margin-left:30px;">
                                        <div style="width:{{(($totalPMSPeriods) * ($totalRows * 20))>1000 ? (((($totalPMSPeriods) * ($totalRows * 20)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 20)) : 13000) : 1000}}px; height:300px;">
                                            @if(count($dataArraySuper)>0)
                                                <canvas id="canvas" width="{{(($totalPMSPeriods) * ($totalRows * 20))>1000 ? (((($totalPMSPeriods) * ($totalRows * 20)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 20)) : 13000): 1000}}px"></canvas>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                        </div>
    @if(Request::segment(1)!='pmscomparisionemployeesiframe')
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <?php //dd(count($dataArraySuper)); ?>
@endsection

@section('pagescripts')
    <script>
        var width = document.getElementById('canvas-container').offsetWidth;
        $("#canvas-inner").css('width',width);


        $("#tabulator-apply").removeClass('hide');

        var table = new Tabulator("#tabulator-apply", {
            columns:[ //set column definitions for imported table data
                {title:"Employee", frozen:true,headerFilter:true},
                @if($filterType != 2)
                    // {title:"Designation", frozen:true},
                @endif
                {title:"Work Location", frozen:true},
                @if($filterType != 1)
                    {title:"Grade", frozen:true},
                @endif
                @if($filterType != 3)
                    {title:"DoA", frozen:true},
                @endif
                {title:"Basic Pay", frozen:true},
                {title:"Duration of Service", frozen:true},
            ],
        });

        'use strict';
        @if(count($dataArraySuper)>0)
        window.chartColors = {
            one: '#E52B50',
            two: '#FFBF00',
            three: '#9966CC',
            four: '#007FFF',
            five: '#89CFF0',
            six: '#000000',
            seven: '#0000FF',
            eight: '#0095B6',
            nine: '#8A2BE2',
            ten: '#DE5D83',
            eleven: '#CD7F32',
            twelve: '#964B00',
            thirteen: '#702963',
            fourteen: '#960018',
            fifteen: '#DE3163',
            sixteen: '#007BA7',
            seventeen: '#7FFF00',
            eighteen: '#7B3F00',
            nineteen: '#0047AB',
            twenty: '#6F4E37',
            twentyone: '#B87333',
            twentytwo: '#FF7F50',
            twentythree: '#DC143C',
            twentyfour: '#00FFFF',
            twentyfive: '#EDC9Af',
            twentysix: '#00CCFF',
            twentyseven: '#3FFF00',
            twentyeight: '#FFD700',
            twentynine: '#808080',
            thirty: '#008000',
            thirtyone: '#00FF3F',
            thirtytwo: '#4B0082',
            thirtythree: '#CCB86B',
            thirtyfour: '#FF00AF',
            thirtyfive: '#B57EDC',
            thirtysix: '#FFF700',
            thirtyseven: '#BFFF00',
            thirtyeight: '#FF00FF',
            thirtynine: '#29AB87',
            forty: '#800000',
            fortyone: '#E0B0FF',
            fortytwo: '#000080',
            fortythree: '#808000',
            fortyfour: '#FF4500',
            fortyfive: '#DA70D6',
            fortysix: '#FFE5B4',
            fortyseven: '#D1E231',
            fortyeight: '#CCCCFF',
            fortynine: '#1C39BB',
            fifty: '#FD6C9E',
            fiftyone: '#8E4585',
            fiftytwo: '#003153',
            fiftythree: '#CC8899',
            fiftyfour: '#800080',
            fiftyfive: '#C71585',
            fiftysix: '#FF007F',
            fiftyseven: '#FA8072',
            fiftyeight: '#92000A',
            fiftynine: '#0F52BA',
            sixty: '#FF2400',
            sixtyone: '#C0C0C0',
            sixtytwo: '#708090',
            sixtythree: '#A7FC00',
            sixtyfour: '#00FF7F',
            sixtyfive: '#D2B48C',
            sixtysix: '#483C32',
            sixtyseven: '#008080',
            sixtyeight: '#40E0D0',
            sixtynine: '#3F00FF',
            seventy: '#7F00FF',
            seventyone: '#40826D',
            seventytwo: '#FFFF00',
        };

        (function(global) {
            var MONTHS = [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ];

            var COLORS = [
                '#4dc9f6',
                '#f67019',
                '#f53794',
                '#537bc4',
                '#acc236',
                '#166a8f',
                '#00a950',
                '#58595b',
                '#8549ba'
            ];

            var Samples = global.Samples || (global.Samples = {});
            var Color = global.Color;

            Samples.utils = {
                // Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
                srand: function(seed) {
                    this._seed = seed;
                },

                rand: function(min, max) {
                    var seed = this._seed;
                    min = min === undefined ? 0 : min;
                    max = max === undefined ? 1 : max;
                    this._seed = (seed * 9301 + 49297) % 233280;
                    return min + (this._seed / 233280) * (max - min);
                },

                numbers: function(config) {
                    var cfg = config || {};
                    var min = cfg.min || 0;
                    var max = cfg.max || 1;
                    var from = cfg.from || [];
                    var count = cfg.count || 8;
                    var decimals = cfg.decimals || 8;
                    var continuity = cfg.continuity || 1;
                    var dfactor = Math.pow(10, decimals) || 0;
                    var data = [];
                    var i, value;

                    for (i = 0; i < count; ++i) {
                        value = (from[i] || 0) + this.rand(min, max);
                        if (this.rand() <= continuity) {
                            data.push(Math.round(dfactor * value) / dfactor);
                        } else {
                            data.push(null);
                        }
                    }

                    return data;
                },

                labels: function(config) {
                    var cfg = config || {};
                    var min = cfg.min || 0;
                    var max = cfg.max || 100;
                    var count = cfg.count || 8;
                    var step = (max - min) / count;
                    var decimals = cfg.decimals || 8;
                    var dfactor = Math.pow(10, decimals) || 0;
                    var prefix = cfg.prefix || '';
                    var values = [];
                    var i;

                    for (i = min; i < max; i += step) {
                        values.push(prefix + Math.round(dfactor * i) / dfactor);
                    }

                    return values;
                },

                months: function(config) {
                    var cfg = config || {};
                    var count = cfg.count || 12;
                    var section = cfg.section;
                    var values = [];
                    var i, value;

                    for (i = 0; i < count; ++i) {
                        value = MONTHS[Math.ceil(i) % 12];
                        values.push(value.substring(0, section));
                    }

                    return values;
                },

                color: function(index) {
                    return COLORS[index % COLORS.length];
                },
            };

            // DEPRECATED
            window.randomScalingFactor = function() {
                return Math.round(Samples.utils.rand(-100, 100));
            };

            // INITIALIZATION

            Samples.utils.srand(Date.now());
        }(this));

        var BarChartData = {
            labels: {!! json_encode($labelArray) !!},
            datasets: [
                <?php $count = 1; foreach($dataArraySuper as $superDataArray=>$dataArray): ?>
                    {
                        label: "{!! $employeeNames[$count-1]!!}",
                        borderColor: window.chartColors.{!! convertNumberToWord($count) !!},
                        backgroundColor: window.chartColors.{!! convertNumberToWord($count) !!},
                        fill: false,
                        data: {!! json_encode($dataArray[0]) !!},
                        yAxisID: 'y-axis-1',
                    },
                <?php $count++; endforeach; ?>
                 ]
        };

        window.onload = function() {
            var ctx = document.getElementById('canvas').getContext('2d');
            window.myLine = Chart.Bar(ctx, {
                data: BarChartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    hoverMode: 'index',
                    stacked: false,
                    title: {
                        display: true,
                        text: 'PMS Score Comparison Report'
                    },
                    scales: {
                        yAxes: [{
                            type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                            display: true,
                            position: 'left',
                            id: 'y-axis-1',
                        }, {
                            type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                            display: true,
                            position: 'right',
                            id: 'y-axis-2',

                            // grid line settings
                            gridLines: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                        }],
                    }
                }
            });
            $("#download-xlsx").click(function(){
                table.download("xlsx", "PMS Score Comparision {{date('Y_m_d H_i_s')}}.xlsx", {sheetName:"My Data"});
            });
        };
        @endif
    </script>
@endsection
