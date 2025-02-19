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
                                        <a href="{{url('fileinput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
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
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2 filter-category" id="DepartmentId">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == app('request')->input('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <label for="CategoryId">Category</label>
                                        <select name="CategoryId" id="CategoryId" class="form-control category">
                                            <option value="">--SELECT A CATEGORY--</option>
                                            @foreach($categories as $category)
                                                <option data-departmentid="{{$category->DepartmentId}}" @if($category->Id == app('request')->input('CategoryId'))selected="selected"@endif value="{{$category->Id}}">{{$category->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <label for="FileName">File Name</label>
                                        <input type="text" id="FileName" name="FileName" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-5 col-md-5 col-sm-5 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('fileindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
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
                                            <tr>
                                                <th width="20">Sl#</th>
                                                <th>Department</th>
                                                <th>Category</th>
                                                <th>File</th>
                                                <th>Visibility Level</th>
                                                <th class="text-center" style="width:200px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                        @forelse($files as $file)
                                            <tr>
                                                <td>{{$slNo++}}. </td>
                                                <td>{{$file->Department}}</td>
                                                <td>{{$file->Category}}</td>
                                                <td><a target="_blank" href="{{asset($file->FilePath)}}">{{$file->Name}}</a></td>
                                                <td>
                                                    @if($file->OrgWide == 1)
                                                        All
                                                    @else
                                                        {{$file->Visibility}}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a class="btn btn-xs btn-primary editconfirm" href="{{url('fileinput',[$file->Id])}}?redirectpage={{app('request')->input('page')}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('filedelete',[$file->Id])}}?redirectpage={{app('request')->input('page')}}"><i class="fa fa-times"></i> Delete</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center"><strong>No data to display.</strong></td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                                {{$files->appends(app('request')->except('page'))->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
