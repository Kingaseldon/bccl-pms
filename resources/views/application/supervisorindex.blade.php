@extends('master')
@section('page-title','Evaluation Groups')
{{--@section('page-header','Your Profile')--}}
@section('page-header',"Manage Evaluation Groups")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{url('supervisorinput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed table-bordered font-small">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Sl#</th>
                                                <th>Name</th>
                                                <th class="text-center" style="width:32%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <?php $slNo=1; ?>
                                        @forelse($supervisors as $supervisor)
                                            <tr>
                                                <td class="text-center">{{$slNo++}}. </td>
                                                <td>{{$supervisor->Name}}</td>
                                                <td class="text-center">
                                                    <a class="btn btn-xs btn-primary editconfirm" href="{{url('supervisorinput',[$supervisor->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('supervisordelete',[$supervisor->Id])}}"><i class="fa fa-times"></i> Delete</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center"><strong>No data to display.</strong></td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

