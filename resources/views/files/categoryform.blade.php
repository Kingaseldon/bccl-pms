@extends('master')
@section('page-title',$update?'Update File Category':'Add File Category')
@section('page-header',$update?'Update File Category':'Add File Category')
@section('action-button')
    @parent
    <a href="{{url('filecategoryindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
                        {{Form::open(['url'=>'savefilecategory'])}}
                            {{Form::hidden('Id',(bool)$category[0]->Id?$category[0]->Id:old('Id'))}}
                            <div class="form-group">
                                <label for="Category">Category <span class="required">*</span></label>
                                <input type="text" id="Category" required="required" name="Name" value="{{(bool)$category[0]->Name?$category[0]->Name:old('Name')}}" autocomplete="off" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="DeptId">For Department <span class="required">*</span></label>
                                <select name="DepartmentId" required="required" id="DeptId" class="form-control select2">
                                    <option value="">--SELECT A DEPARTMENT--</option>
                                    @foreach($departments as $department)
                                        <option value="{{$department->Id}}" @if($department->Id == ((bool)$category[0]->DepartmentId?$category[0]->DepartmentId:'zz'))selected="selected"@endif>{{$department->Name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Status">Status <span class="required">*</span></label>
                                {{Form::select("Status",['1'=>"Active",'0'=>"In-Active"],$category[0]->Status,['id'=>"Status",'class'=>"form-control",'required'=>'required'])}}
                            </div>
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            <a href="{{url('filecategoryindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
