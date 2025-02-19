@extends('master')
@section('page-title','Designations')
@section('page-header',"Manage Designations")
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
                                        <a href="{{url('designationinput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
                                        <br><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{Request::url()}}" method="GET">
                            <div class="row">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label for="Name" class="control-label">Designation Name</label>
                                        <input type="text" autocomplete="off" name="Name" value="{{app('request')->input('Name')}}" id="Name" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-4 col-md-4 col-sm-4 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('designationindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                <table class="table table-striped table-condensed table-bordered font-small">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:45px;">Sl #</th>
                                            <th>Designation</th>
                                            <th class="text-center" style="width:32%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                    @forelse($designations as $designation)
                                        <tr>
                                            <td class="text-center">{{$slNo++}}. </td>
                                            <td>{{$designation->Name}}</td>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-primary editconfirm" href="{{url('designationinput',[$designation->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('designationdelete',[$designation->Id])}}"><i class="fa fa-times"></i> Delete</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center"><strong>No data to display.</strong></td>
                                        </tr>
                                    @endforelse
                                </table>
                                {{$designations->appends(app('request')->except('page'))->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
