@extends('master')
@section('page-title',$update?'Update Section':'Add Section')
@section('page-header',$update?'Update Section':'Add Section')
@section('action-button')
    @parent
    <a href="{{url('sectionindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-4 card dashboard-product">
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
                        {{Form::open(['url'=>'savesection'])}}
                            {{Form::hidden('Id',$section['Id']??old('Id'))}}
                            <div class="form-group">
                                <label for="Section">Section <span class="required">*</span></label>
                                <input type="text" id="Section" required="required" name="Name" value="{{$section['Name']??old('Name')}}" autocomplete="off" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="DeptId">Under <span class="required">*</span></label>
                                <select name="DepartmentId" required="required" id="DeptId" class="form-control select2">
                                    <option value="">--SELECT A DEPARTMENT--</option>
                                    @foreach($departments as $department)
                                        <option value="{{$department->Id}}" @if($department->Id == ($section['DepartmentId']??'zz'))selected="selected"@endif>{{$department->Name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Status">Status <span class="required">*</span></label>
                                <select name="Status" required="required" id="Status" class="form-control">
                                    <option value="1" @if(isset($section['Status']) && $section['Status'] == 1)selected="selected"@endif>Active</option>
                                    <option value="0" @if(isset($section['Status']) && $section['Status'] === 0)selected="selected"@endif>In-Active</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            <a href="{{url('sectionindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
