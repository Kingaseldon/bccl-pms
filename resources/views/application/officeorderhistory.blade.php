@extends('master')
@section('page-title','Office Order History')
@section('page-header',"Office Order History")
@section('pagescripts')
    <script>
        $( function() {
            $( "#accordion" ).accordion({
                collapsible: true,
                heightStyle: "content",
                active: false
            });
        } );
    </script>
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-3 offset-md-9">
                                <div class="form-group row">
                                    <label for="SearchBox" class="col-sm-3 control-label text-right" style="padding-right:0;padding-top:4px;"><strong>Search: </strong></label>
                                    <div class="col-sm-9" style="padding-left:5px;">
                                        <input type="text" id="SearchBox" class="form-control input-sm"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="accordion">
                                    @foreach($officeOrders as $officeOrder)
                                        <h6><strong>{{$officeOrder['Period']}}</strong></h6>
                                        <div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-condensed large-padding font-small" style="margin-bottom:0;">
                                                    <thead>
                                                    <tr>
                                                        <th style="width:20px;">Sl.#</th>
                                                        <th>Employee</th>
                                                        <th>Department</th>
                                                        <th>Designation (Current)</th>
                                                        <th>Grade / Step (Current)</th>
                                                        <th>Outcome</th>
                                                        <th>Office Order</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php $slNo = 1; ?>
                                                    @forelse($officeOrder['OfficeOrders'] as $singleOfficeOrder)
                                                        <tr>
                                                            <td>{{$slNo++}}</td>
                                                            <td><a href="{{url('viewprofile',[$singleOfficeOrder->Id])}}" target="_blank"><strong>{{$singleOfficeOrder->Name}}</strong></a></td>
                                                            <td>{{$singleOfficeOrder->Department}}</td>
                                                            <td>{{$singleOfficeOrder->Designation}}</td>
                                                            <td>{{$singleOfficeOrder->GradeStep}}</td>
                                                            <td>{{$singleOfficeOrder->Outcome}}</td>
                                                            <td><center><a href="{{url('filedownload')}}?file={{$singleOfficeOrder->OfficeOrderPath}}&ver={{randomString().randomString()}}" target="_blank" class="btn btn-xs btn-inverse-warning"><i class="fa fa-download"></i> Office Order</a></center></td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7"><center>No data to display!</center></td>
                                                        </tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection