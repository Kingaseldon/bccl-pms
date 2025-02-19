@extends('master')
@section('page-title',"Open PMS")
@section('page-header',"Open PMS")
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-6 card dashboard-product text-center">
                        @if($status == 3)
                            <div class="col-md-4">
                                <a href="{{url('openpmsprocess?type=2')}}" style="" class="openconfirm btn btn-inverse-danger"><i class="fa fa-times" style="font-size:12px;"></i> Open PMS Round {{$currentPMSRound}} for Appraisers and Admin</a>
                            </div>
                        @endif

                        @if($status == 2)
                            <div class="col-md-4">
                                <a href="{{url('openpmsprocess?type=1')}}" style="" class="openconfirm btn btn-inverse-danger"><i class="fa fa-times" style="font-size:12px;"></i> Open PMS Round {{$currentPMSRound}} for Submission</a>
                            </div>
                        @endif

                        @if(!in_array($status,[2,3]))
                            <div class="col-md-12">
                                <strong>PMS Round {{$currentPMSRound}} is currently open.</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
