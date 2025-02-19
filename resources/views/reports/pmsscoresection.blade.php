@extends('master')
@section('page-title','Section Wise Comparision')
@section('page-header',"Section Wise Comparision")

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 0;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{Request::url()}}" method="GET">
                            <div class="row">
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2 fetch-employee-on-dept" id="filter-section">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == app('request')->input('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->Name}}</option>
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
                                    <a href="{{url('sectionwiseperformance')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a> &nbsp;
                                    <button id="download-xlsx" type="button" class="btn btn-success"><i class="fa fa-file-excel-o"></i> &nbsp;Export to Excel</button>
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
                                                <th>Department</th>
                                                <th>Section</th>
                                                <?php $labelArray = []; $dataArray = []; ?>
                                                @foreach($pmsPeriods as $pmsPeriod)
                                                    @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                                                        <?php array_push($labelArray,date_format(date_create($pmsPeriod->StartDate),"M, Y")); ?>
                                                        <th>{{date_format(date_create($pmsPeriod->StartDate),"M, Y")}}</th>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $slNo = 1; $dataArraySuper = []; $employeeNames = []; ?>
                                            @forelse($result as $reportData)
                                                <?php array_push($employeeNames,$reportData->Section); ?>
                                                <tr style="border-bottom:2px solid black;">
                                                    <td>{{$reportData->Department}}</td>
                                                    <td>{!! $reportData->Section !!}</td>
                                                    <?php $dataArraySub = []; $dataArray = [] ?>
                                                    @foreach($pmsPeriodArray as $pmsId)
                                                        <?php $scoreVar = "$pmsId Score"; ?>
                                                        <?php array_push($dataArray,number_format(doubleval($reportData->$scoreVar),2)); ?>
                                                        <td>{{number_format(doubleval($reportData->$scoreVar),2)}}</td>
                                                    @endforeach
                                                    <?php array_push($dataArraySub,$dataArray); ?>
                                                </tr>
                                                <?php array_push($dataArraySuper,$dataArraySub); ?>
                                            @empty

                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php $totalPMSPeriods = count($pmsPeriodArray); $totalRows = count($result); ?>
                            <div id="canvas-container" class="row" style="text-align:center;width:100%;">
                                <div id="canvas-inner" style="overflow-x:scroll; margin-left:30px;" >
                                    <div style="width:{{(($totalPMSPeriods) * ($totalRows * 20))>1000 ? (((($totalPMSPeriods) * ($totalRows * 20)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 20)) : 13000) : 1000}}px; height:300px;">
                                        <canvas id="canvas" height="300px" width="{{(($totalPMSPeriods) * ($totalRows * 20))>1000 ? (((($totalPMSPeriods) * ($totalRows * 20)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 20)) : 13000): 1000}}px"></canvas>
                                    </div>
                                </div>
                                <br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php //dd(count($dataArraySuper)); ?>
@endsection

@section('pagescripts')
    <script>
        var width = document.getElementById('canvas-container').offsetWidth;
        $("#canvas-inner").css('width',width);

        $("#tabulator-apply").removeClass('hide');
        var table = new Tabulator("#tabulator-apply", {
            columns:[ //set column definitions for imported table data
                {title:"Department", frozen:true,headerFilter:true},
                {title:"Section", frozen:true},
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

        // HERE
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
                    hoverMode: 'index',
                    stacked: false,
                    title: {
                        display: true,
                        text: 'Section Wise Comparison Report'
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
                table.download("xlsx", "Section Wise Comparison Report {{date('Y_m_d H_i_s')}}.xlsx", {sheetName:"My Data"});
            });
        };
        @endif
    </script>
@endsection
