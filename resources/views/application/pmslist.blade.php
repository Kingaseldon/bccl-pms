@extends('master')
@section('page-title','Track My Current PMS')
@section('page-header',"Track My Current PMS")
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $noDisplay = false; ?>
                        <div class="row">
                            @if(isset($status) && ($status == 2 || $status == 3))
                                <div class="col-md-12">
                                    <p style="color:#fff;font-weight:bold;">{!! trim($closedMessage) !!}</p>
                                </div>
                            @else
                                @if($notWithinPMSPeriod)
                                    <div class="col-md-12">
                                        <p style="color:#fff;"><strong>The last PMS Period is over.</strong></p>
                                    </div>
                                @else
                                    @if($alreadySubmitted)
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table class="table table-condensed table-bordered less-padding">
                                                    <thead>
                                                    <tr>
                                                        <th>History</th>
                                                        @if(($pmsDetails[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $pmsDetails[0]->StatusByEmployeeId == Auth::user()->Id) || $pmsDetails[0]->LastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER || (!$hasLevel2 && $pmsDetails[0]->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER))
                                                            <th class="text-center" style="width:2%;">Submit</th>
                                                        @endif
{{--                                                        @if(isset($pmsDetails[0]->PMSOutcome) && (bool)$pmsDetails[0]->PMSOutcome)--}}
                                                            <th class="text-center" style="width:2%;">View</th>
                                                        {{--@endif--}}
                                                    </tr>
                                                    </thead>
                                                    <?php $colspan=4; $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                                    @forelse($pmsDetails as $pmsDetail)
                                                        <tr>
                                                            <td>
                                                                <table style="width:100%;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <th><strong>Action Date</strong></th>
                                                                        <th><strong>Details</strong></th>
                                                                        <th><strong>Action by</strong></th>
                                                                    </tr>
                                                                    @foreach($pmsHistory as $history)
                                                                        <tr>
                                                                            <td>{{convertDateTimeToClientFormat($history->StatusUpdateTime)}}</td>
                                                                            <td>Status: {{$history->Status}}
                                                                                @if($history->StatusUpdatedById != Auth::user()->Id)
                                                                                    <br/>Remarks: <em>{!! (bool)$history->Remarks?"".$history->Remarks:' -- ' !!}</em>
                                                                                @endif
                                                                            </td>
                                                                            <td>@if($history->PMSStatusId == CONST_PMSSTATUS_VERIFIED)@if((bool)$history->Level1MultipleNames){{$history->Level1MultipleNames}}@else{{$history->StatusUpdatedBy}} ({{$history->LastStatusDesignation}})@endif @elseif($history->PMSStatusId == CONST_PMSSTATUS_APPROVED)@if((bool)$history->Level2MultipleNames){{$history->Level2MultipleNames}}@else{{$history->StatusUpdatedBy}} ({{$history->LastStatusDesignation}})@endif @else{{$history->StatusUpdatedBy}} ({{$history->LastStatusDesignation}}) @endif </td>
                                                                        </tr>
                                                                    @endforeach
                                                                    @if(isset($pmsDetail->PMSOutcome) && (bool)$pmsDetail->PMSOutcome)
                                                                        @if(($pmsDetail->PMSOutcomeId == CONST_PMSOUTCOME_NOACTION) || ($pmsDetail->PMSOutcomeId != CONST_PMSOUTCOME_NOACTION && $pmsDetail->OfficeOrderEmailed == 1))
                                                                        <tr>
                                                                            <td>{{$pmsDetail->OutcomeDateTime}}</td>
                                                                            <td>Result - {{$pmsDetail->PMSOutcome}} <br/>
                                                                                @if((bool)$pmsDetail->OfficeOrderPath)
                                                                                    <a href="{{url('filedownload')}}?file={{$pmsDetail->OfficeOrderPath}}&ver={{randomString().randomString()}}" target="_blank" class="btn btn-xs btn-inverse-warning"><i class="fa fa-download"></i> Office Order</a>
                                                                                @endif
                                                                            </td>
                                                                            <td>Management</td>
                                                                        </tr>
                                                                        @endif
                                                                    @endif
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                            {{--<td>--}}


                                                            {{--</td>--}}
                                                            {{--@if($pmsDetail->LastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER || (!$hasLevel2 && $pmsDetail->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER))--}}
                                                            @if(($pmsDetails[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $pmsDetails[0]->StatusByEmployeeId == Auth::user()->Id) || $pmsDetails[0]->LastStatusId == CONST_PMSSTATUS_SENTBACKBYVERIFIER || (!$hasLevel2 && $pmsDetails[0]->LastStatusId == CONST_PMSSTATUS_SENTBACKBYAPPROVER))
                                                                <?php $colspan = 3; ?>
                                                                <td class="text-center" style="vertical-align: middle;">
                                                                    <a class="btn btn-xs btn-primary" href="{{url('resubmit',[$pmsDetail->Id])}}"><i class="fa fa-edit"></i> @if($pmsDetails[0]->LastStatusId == CONST_PMSSTATUS_DRAFT){{"Submit"}}@else{{"Resubmit"}}@endif</a>
                                                                </td>
                                                            @endif
{{--                                                            @if(isset($pmsDetail->PMSOutcome) && (bool)$pmsDetail->PMSOutcome)--}}
                                                                <td class="text-center" style="vertical-align: middle;">
                                                                    <a href="{{url('viewpmsdetails',[$pmsDetail->Id])}}" class="btn btn-xs btn-success"><i class="fa fa-eye"></i> View Evaluation Details</a>
                                                                </td>
                                                            {{--@endif--}}
                                                        </tr>
                                                    @empty
                                                        <?php $noDisplay = true; ?>
                                                        <tr>
                                                            <td colspan="3" class="text-center"><strong>No data to display.</strong></td>
                                                        </tr>
                                                    @endforelse
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <?php $noDisplay = true; ?>
                                        <div class="col-md-12">
                                            <p style="color:#fff;"><strong>You have not submitted PMS yet for this period.</strong></p>
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>
                        @if(!$noDisplay)
                            @include('includes.resultlegends')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
