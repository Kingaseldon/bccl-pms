@extends('master')
@section('page-title',$update?'Update Department':'Add Department')
@section('page-header',$update?'Update Department':'Add Department')
@section('action-button')
    @parent
    <a href="{{url('departmentindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
                        {{Form::open(['url'=>'savedepartment'])}}
                            {{Form::hidden('Id',$department['Id']??old('Id'))}}
                            <div class="form-group">
                                <label for="ShortName">Short Name <span class="required">*</span></label>
                                <input type="text" id="ShortName" required="required" name="ShortName" value="{{$department['ShortName']??old('ShortName')}}" autocomplete="off" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="Dept">Name <span class="required">*</span></label>
                                <input type="text" id="Dept" required="required" name="Name" value="{{$department['Name']??old('Name')}}" autocomplete="off" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="Status">Status <span class="required">*</span></label>
                                <select name="Status" required="required" id="Status" class="form-control">
                                    <option value="1" @if(isset($department['Status']) && $department['Status'] == 1)selected="selected"@endif>Active</option>
                                    <option value="0" @if(isset($department['Status']) && $department['Status'] === 0)selected="selected"@endif>In-Active</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            <a href="{{url('departmentindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
