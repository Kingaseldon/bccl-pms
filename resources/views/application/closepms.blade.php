@extends('master')
@section('page-title',"Close PMS")
@section('page-header',"Close PMS")
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-6 card dashboard-product text-center">
                        @if($status != 2 && $status != 3)
                            <div class="col-md-4">
                                <a href="{{url('closepmsprocess?type=2')}}" style="" class="closeconfirm btn btn-inverse-danger"><i class="fa fa-times" style="font-size:12px;"></i> Close PMS Round {{$currentPMSRound}} for Submission</a>
                            </div>
                        @else
                            @if($status == 3)
                                <div class="col-md-12">
                                    <strong>PMS Round {{$currentPMSRound}} has already been closed.</strong>
                                </div>
                            @else
                                <div class="col-md-4">
                                    <a href="{{url('closepmsprocess?type=3')}}" style="" class="closeconfirm btn btn-inverse-danger"><i class="fa fa-times" style="font-size:12px;"></i> Close PMS Round {{$currentPMSRound}} for Appraisers and Admin</a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
