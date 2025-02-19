@extends('master')
@section('page-title','Sections')
{{--@section('page-header','Your Profile')--}}
@section('page-header',"Manage Sections")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            {{--<br><br><br><br>--}}
            <div class="row"> {{--here--}}
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{url('sectioninput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
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
                                {{--<div class="col-sm-12">--}}
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2" id="DepartmentId">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == app('request')->input('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label for="Name" class="control-label">Section Name</label>
                                        <input type="text" autocomplete="off" name="Name" value="{{app('request')->input('Name')}}" id="Name" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <div class="form-group">
                                        <label for="Status">Section Status </label>
                                        <select name="Status" id="Status" class="form-control">
                                            <option value="">All</option>
                                            <option value="1" @if(app('request')->has('Status') && app('request')->input('Status') == 1)selected="selected"@endif>Active</option>
                                            <option value="0" @if(app('request')->has('Status') && app('request')->input('Status') == 0)selected="selected"@endif>In-Active</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-3 col-md-4 col-sm-4 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('sectionindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
                                        </div>
                                    </div>
                                </div>
                                {{--</div>--}}
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                <table class="table table-striped table-condensed table-bordered font-small">
                                    <thead>
                                        {{--<tr>--}}
                                            {{--<th>Sl. #</th>--}}
                                            {{--<th>Name</th>--}}
                                            {{--<th>Email</th>--}}
                                            {{--<th>Extension</th>--}}
                                            {{--<th>Designation</th>--}}
                                            {{--<th>Department</th>--}}
                                            {{--<th>Role</th>--}}
                                            {{--<th>Status</th>--}}
                                        {{--</tr>--}}
                                        <tr>
                                            <th class="text-center">Sl#</th>
                                            <th>Section</th>
                                            <th>Department</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" style="width:32%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                    @forelse($sections as $section)
                                        <tr>
                                            <td class="text-center">{{$slNo++}}. </td>
                                            <td>{{$section->Section}}</td>
                                            <td>{{$section->Department}}</td>
                                            <td class="text-center">{{$section->Status}}</td>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-primary editconfirm" href="{{url('sectioninput',[$section->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('sectiondelete',[$section->Id])}}"><i class="fa fa-times"></i> Delete</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center"><strong>No data to display.</strong></td>
                                        </tr>
                                    @endforelse
                                </table>
                                    {{$sections->appends(app('request')->except('page'))->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
