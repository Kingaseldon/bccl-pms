@extends('master')
{{--@section('page-title','My PMS Evaluation History')--}}
@section('page-header',"My PMS Evaluation History")
@section('content')
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
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered less-padding">
                                        <thead>
                                        <tr>
                                            <th width="20">Sl#</th>
                                            <th>PMS Period</th>
                                            <th>Score</th>
                                            <th>Result</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $slNo = 1; $labelArray = []; $scoreArray = []; $historyCount = count($history); $rowCount = 1;?>
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
                                                <td>PMS {{$data->PMSNumber}};  {{$period}}<?php array_push($labelArray,'PMS '.$data->PMSNumber); ?></td>
                                                <td>
                                                    {{$data->PMSScore}}<?php array_push($scoreArray,$data->PMSScore); ?>
                                                    @if((bool)$data->PMSSubmissionId)
                                                        <a href="{{url('viewpmsdetails',[$data->PMSSubmissionId,3])}}"class="btn btn-xs btn-success pull-right"><i class="fa fa-eye"></i> View Evaluation Details</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($rowCount == $historyCount)
                                                        @if((bool)$data->OfficeOrderPath)
                                                            @if($data->OfficeOrderEmailed == 1)
                                                                {{$data->PMSResult}} &nbsp; @if((bool)$data->PMSRemarks)<br/>{{$data->PMSRemarks}}@endif
                                                                @if((bool)$data->OfficeOrderPath)&nbsp;<a href="{{url('filedownload')}}?file={{$data->OfficeOrderPath}}&ver={{randomString().randomString()}}" target="_blank" class="btn btn-xs btn-inverse-warning"><i class="fa fa-download"></i> Office Order</a>@endif
                                                            @else
                                                                {{--No Action--}}
                                                                {{$data->PMSResult ? $data->PMSResult : "No Action"}}
                                                            @endif
                                                        @else
                                                            {{--No Action--}}
                                                            {{$data->PMSResult ? $data->PMSResult : "No Action"}}
                                                        @endif
                                                    @else
                                                        {{$data->PMSResult ? $data->PMSResult : "No Action"}} &nbsp; @if((bool)$data->PMSRemarks)<br/>{{$data->PMSRemarks}}@endif
                                                        @if((bool)$data->OfficeOrderPath)&nbsp;<a href="{{url('filedownload')}}?file={{$data->OfficeOrderPath}}&ver={{randomString().randomString()}}" target="_blank" class="btn btn-xs btn-inverse-warning"><i class="fa fa-download"></i> Office Order</a>@endif
                                                    @endif
                                                </td>
                                            </tr>
                                            <?php $rowCount++; ?>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No data to display!</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php $totalPMSPeriods = count($history); $totalRows = 1; ?>
                            <div id="canvas-container" class="row" style="text-align:center;width:100%;">
                                <div class="canvas-inner" style="overflow-x:scroll; margin-left:30px;" >
                                    <div style="width:{{(($totalPMSPeriods) * ($totalRows * 14))>1100 ? (((($totalPMSPeriods) * ($totalRows * 14)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 14)) : 13000) : 1100}}px; height:300px;">
                                        <canvas id="canvas" height="300px" width="{{(($totalPMSPeriods) * ($totalRows * 14))>1100 ? (((($totalPMSPeriods) * ($totalRows * 14)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 14)) : 13000): 1100}}px"></canvas>
                                    </div>
                                </div>
                                <br>
                                <div class="canvas-inner" style="overflow-x:scroll; margin-left:30px;" >
                                    <div style="width:{{(($totalPMSPeriods) * ($totalRows * 14))>1100 ? (((($totalPMSPeriods) * ($totalRows * 14)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 14)) : 13000) : 1100}}px; height:300px;">
                                        <canvas id="lineChartCanvas" height="300px" width="{{(($totalPMSPeriods) * ($totalRows * 14))>1100 ? (((($totalPMSPeriods) * ($totalRows * 14)) < 13000 ) ? (($totalPMSPeriods) * ($totalRows * 14)) : 13000): 1100}}px"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6 col-xs-12 col-lg-6">
                                <h5 style="font-size:17px;color:#fff;">Score Interpretations</h5>
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered">
                                        <thead>
                                        <tr>
                                            <th class="text-center" style="width:20px;">Category</th>
                                            <th class="text-center">Scale</th>
                                            <th class="text-center">Standard</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">1</td>
                                                <td class="text-center">90 - 100</td>
                                                <td class="text-left">Star</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">2</td>
                                                <td class="text-center">80 – 89.99</td>
                                                <td class="text-left">Outstanding</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">3</td>
                                                <td class="text-center">65 – 79.99</td>
                                                <td class="text-left">Good</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">4</td>
                                                <td class="text-center">50 – 64.99</td>
                                                <td class="text-left">Meets Expectation</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">5</td>
                                                <td class="text-center">49.99 and less</td>
                                                <td class="text-left">Below Expectation</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @include('includes.resultlegends')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('pagescripts')
    <script>
        var width = document.getElementById('canvas-container').offsetWidth;
        $(".canvas-inner").css('width',width);
        var BarChartData = {
            labels: {!! json_encode($labelArray) !!},
            datasets: [
                {
                    label: "My PMS Score",
                    borderColor: 'orange',
                    backgroundColor: 'orange',
                    fill: false,
                    data: {!! json_encode($scoreArray) !!},
                    yAxisID: 'y-axis-1',
                },
            ]
        };

        window.onload = function() {
            var ctx = document.getElementById('canvas').getContext('2d');
            var LineChart = document.getElementById('lineChartCanvas').getContext('2d');
            window.myLine = Chart.Bar(ctx, {
                data: BarChartData,
                options: {
                    responsive: true,
                    hoverMode: 'index',
                    stacked: false,
                    title: {
                        display: true,
                        text: 'Bar Graph Representation of my Performance'
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
            window.myLine = Chart.Line(LineChart, {
                data: BarChartData,
                options: {
                    responsive: true,
                    hoverMode: 'index',
                    stacked: false,
                    title: {
                        display: true,
                        text: 'Line Chart Representation of my Performance'
                    },
                    scales: {
                        yAxes: [
                            {
                                type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                                display: true,
                                position: 'left',
                                id: 'y-axis-1',
                            },
                            {
                                type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                                display: true,
                                position: 'right',
                                id: 'y-axis-2',

                                // grid line settings
                                gridLines: {
                                    drawOnChartArea: false, // only want the grid lines for one axis to show up
                                },
                            }
                        ],
                    }
                }
            });

        }
    </script>
@endsection
