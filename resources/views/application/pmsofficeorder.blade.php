@extends('master')
@section('page-title','Generate office order')
@section('page-header',"Generate office order")
@section('pagescripts')
    @if(isset($status) && $status == 1)
        <script>
            $(function(){
                $( "#accordion" ).accordion({
                    collapsible: true,
                    heightStyle: "content",
                    @if((bool)$selectIndex)
                        active: {{$selectIndex}}
                    @else
                        active: false
                    @endif
                });
            });
        </script>
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        @if(isset($status) && $status == 3)
                            <p style="color:#fff;font-weight:bold;">{!! trim($closedMessage) !!}</p>
                        @else
                        <div class="row">
                            <div class="col-md-12">
                                    <div id="accordion" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
                                        @forelse($departments as $department)
                                            <h6><strong>{{$department->Name}}</strong></h6>
                                            <div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-condensed large-padding" style="margin-bottom:0;">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:20px;">Sl.#</th>
                                                                <th>Employee</th>
                                                                <th>Section</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $slNo = 1; ?>
                                                            @foreach($employees[$department->Id] as $employee)
                                                                <tr>
                                                                    <td>{{$slNo++}}</td>
                                                                    <td>{{$employee->Name}}</td>
                                                                    <td>{{$employee->Section}}</td>
                                                                    <td class="" style="padding: 0.45rem 0.4rem 0.4rem 0.4rem;">
                                                                        <span class="text-left">{{$employee->Outcome}}</span>
                                                                    </td>
                                                                    <td>
                                                                        @if((bool)$employee->OfficeOrderPath)&nbsp;<a target="_blank" class="btn btn-inverse-warning btn-xs" href="{{asset($employee->OfficeOrderPath)}}?ver={{randomString().randomString()}}">Download Office Order</a> | <a data-email="{{$employee->Email}}" href="{{url('emailofficeorder')}}?file={{asset($employee->OfficeOrderPath)}}&empId={{$employee->EmployeeId}}" class="btn <?php if($employee->OfficeOrderEmailed == 1): ?>btn-inverse-success<?php else: ?>btn-inverse-default<?php endif; ?> btn-xs emailofficeorder"><?php if($employee->OfficeOrderEmailed == 1): ?><i class="fa fa-refresh"></i> Re-<?php endif; ?>Send office order in Email</a>@endif </span>&nbsp; <a href="{{url('officeorder',[$employee->SubmissionId])}}" class="text-right pull-right btn-xs btn btn-primary"><?php if((bool)$employee->OfficeOrderPath): ?>{{"Re-"}}<?php endif; ?>Generate Office Order</a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <br><br>
                                            </div>
                                        @empty
                                            <p style="color:#fff;"><strong>No pending office orders.</strong></p>
                                        @endforelse
                                        {{--<h6 class="ui-accordion-header ui-corner-top ui-state-default ui-accordion-icons ui-accordion-header-active ui-state-active" role="tab" id="ui-id-11" aria-controls="ui-id-12" aria-selected="true" aria-expanded="true" tabindex="0"><span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-s"></span><strong>MIS</strong></h6>--}}
                                        {{--<div class="ui-accordion-content ui-corner-bottom ui-helper-reset ui-widget-content ui-accordion-content-active" id="ui-id-12" aria-labelledby="ui-id-11" role="tabpanel" aria-hidden="false" style="display: block;">--}}
                                            {{--<div class="table-responsive">--}}
                                                {{--<table class="table table-bordered table-condensed large-padding" style="margin-bottom:0;">--}}
                                                    {{--<thead>--}}
                                                    {{--<tr>--}}
                                                        {{--<th>Sl.#</th>--}}
                                                        {{--<th>Employee</th>--}}
                                                        {{--<th>Position</th>--}}
                                                        {{--<th>Status</th>--}}
                                                    {{--</tr>--}}
                                                    {{--</thead>--}}
                                                    {{--<tbody>--}}
                                                    {{--<tr>--}}
                                                        {{--<td>4</td>--}}
                                                        {{--<td>Yeshi Norbu (Manager , CBS &amp;PRM Section)</td>--}}
                                                        {{--<td>Head of Section</td>--}}
                                                        {{--<td class="" style="padding: 0.45rem 0.4rem 0.4rem 0.4rem;">--}}
                                                            {{--<span class="text-left">Promotion Awarded </span>&nbsp; <a href="{{url('officeorder')}}" class="text-right pull-right btn-xs btn btn-primary">Generate Office Order</a>--}}
                                                        {{--</td>--}}
                                                    {{--</tr>--}}
                                                    {{--<tr>--}}
                                                        {{--<td>7</td>--}}
                                                        {{--<td>Pema Dorji (CBS Engineer)</td>--}}
                                                        {{--<td>P2 - Reporting directly to HoD</td>--}}
                                                        {{--<td class="" style="padding: 0.45rem 0.4rem 0.4rem 0.4rem;">--}}
                                                            {{--<span class="text-left">Letter of Appreciation Awarded </span>&nbsp; <a href="{{url('officeorder')}}" class="text-right pull-right btn-xs btn btn-primary">Generate Office Order</a>--}}
                                                        {{--</td>--}}
                                                    {{--</tr>--}}
                                                    {{--<tr>--}}
                                                        {{--<td>9</td>--}}
                                                        {{--<td>Dechen Dorji (ERP Engineer)</td>--}}
                                                        {{--<td>P2 - Reporting to Head of Section</td>--}}
                                                        {{--<td class="" style="padding: 0.45rem 0.4rem 0.4rem 0.4rem;">--}}
                                                            {{--<span class="text-left">Single Increment Awarded </span>&nbsp; <a href="{{url('officeorder')}}" class="text-right pull-right btn-xs btn btn-primary">Generate Office Order</a>--}}
                                                        {{--</td>--}}
                                                    {{--</tr>--}}

                                                    {{--<tr>--}}
                                                        {{--<td>8</td>--}}
                                                        {{--<td>Sangay Wangdi Moktan (Application Developer)</td>--}}
                                                        {{--<td>P2 - Reporting directly to HoD</td>--}}
                                                        {{--<td class="" style="padding: 0.45rem 0.4rem 0.4rem 0.4rem;">--}}
                                                            {{--<span class="text-left">Single Increment Awarded </span>&nbsp; <a href="{{url('officeorder')}}" class="text-right pull-right btn-xs btn btn-primary">Generate Office Order</a>--}}
                                                        {{--</td>--}}
                                                    {{--</tr>--}}
                                                    {{--</tbody>--}}
                                                {{--</table>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection