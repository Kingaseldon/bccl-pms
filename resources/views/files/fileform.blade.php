@extends('master')
@section('page-title',$update?'Update File':'Add File')
@section('page-header',$update?'Update File':'Add File')
@section('action-button')
    @parent
    <a href="{{url('fileindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-6 card dashboard-product">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(Session::has('message'))
                            <h6><i class="fa fa-times-circle" style="color:red"></i> {!!Session::get('message')!!}</h6>
                        @endif
                        {{Form::open(['url'=>'savefile','files'=>true])}}
                            {{Form::hidden('Id',(bool)$file[0]->Id?$file[0]->Id:old('Id'))}}
                            {{Form::hidden("RedirectPage",app('request')->input('redirectpage'))}}
                            <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="DeptId">For Department / Unit <span class="required">*</span></label>
                                    <select name="DepartmentId" required="required" id="DeptId" class="form-control select2 filter-category">
                                        <option value="">--SELECT A DEPARTMENT--</option>
                                        @foreach($departments as $department)
                                            <option value="{{$department->Id}}" @if($department->Id == ((bool)$file[0]->DepartmentId?$file[0]->DepartmentId:old('DepartmentId')))selected="selected"@endif>{{$department->Name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="CategoryId">Category <span class="required">*</span></label>
                                    <select name="CategoryId" required="required" id="CategoryId" class="form-control category">
                                        <option value="">--SELECT A CATEGORY--</option>
                                        @foreach($categories as $category)
                                            <option data-departmentid="{{$category->DepartmentId}}" @if($category->Id == ((bool)$file[0]->CategoryId?$file[0]->CategoryId:old('CategoryId')))selected="selected"@endif value="{{$category->Id}}">{{$category->Name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Name">File Name <span class="required">*</span></label>
                                    <input type="text" id="Name" required="required" name="Name" value="{{(bool)$file[0]->Name?$file[0]->Name:old('Name')}}" autocomplete="off" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="VisibilityLevel">Visible to Department/Unit(s) </label>
                                    <select name="VisibilityLevel[]" id="VisibilityLevel" multiple class="form-control select2multiple2">
                                        @foreach($departments as $department)
                                            <option value="{{$department->Id}}" @if(in_array($department->Id,$filedepartments))selected="selected"@endif>{{$department->Name}}</option>
                                        @endforeach
                                        <option value="99" @if(in_array(99,$filedepartments))selected="selected"@endif>Management Team</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Status">Status <span class="required">*</span></label>
                                    {{Form::select("Status",['1'=>"Active",'0'=>"In-Active"],$file[0]->Status,['id'=>"Status",'class'=>"form-control",'required'=>'required'])}}
                                </div>
                                @if((bool)$file[0]->FilePath)
                                    <a href="{{asset($file[0]->FilePath)}}" class="btn btn-primary"><i class="fa fa-download"></i> {{$file[0]->Name}}</a><br>
                                @endif
                                <div class="form-group">
                                    <label for="File">File @if((bool)$file[0]->Id)(OVERWRITE)@else<span class="required">*</span>@endif</label>
                                    <input type="file" id="File" @if(!(bool)$file[0]->Id)required="required"@endif accept=".pdf" name="File" autocomplete="off" class="form-control guideline-doc"/>
                                </div>
                            </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            <a href="{{url('fileindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
