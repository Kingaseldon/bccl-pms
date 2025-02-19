@extends('master')
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row"> {{--here--}}
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{url('filecategoryinput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
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
                                <div class="col-md-6 col-lg-2">
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
                                        <label for="Name" class="control-label">Category</label>
                                        <input type="text" autocomplete="off" name="Name" value="{{app('request')->input('Name')}}" id="Name" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-5 col-md-5 col-sm-5 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('filecategoryindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
                                        </div>
                                    </div>
                                </div>
                                {{--</div>--}}
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed table-bordered font-small">
                                        <thead>
                                            <tr>
                                                <th width="20">Sl#</th>
                                                <th>Department</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th class="text-center" style="width:200px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                        @forelse($categories as $category)
                                            <tr>
                                                <td>{{$slNo++}}. </td>
                                                <td>{{$category->Department}}</td>
                                                <td>{{$category->Name}}</td>
                                                <td>{{$category->Status}}</td>
                                                <td class="text-center">
                                                    <a class="btn btn-xs btn-primary editconfirm" href="{{url('filecategoryinput',[$category->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('filecategorydelete',[$category->Id])}}"><i class="fa fa-times"></i> Delete</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center"><strong>No data to display.</strong></td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                                {{$categories->appends(app('request')->except('page'))->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
