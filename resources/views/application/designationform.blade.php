@extends('master')
@section('page-title',$update?'Update Designation':'Add Designation')
@section('page-header',$update?'Update Designation':'Add Designation')
@section('action-button')
    @parent
    <a href="{{url('designationindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
                        {{Form::open(['url'=>'savedesignation'])}}
                            {{Form::hidden('Id',$designation['Id']??old('Id'))}}
                            <div class="form-group">
                                <label for="Dept">Designation <span class="required">*</span></label>
                                <input type="text" id="Dept" required="required" name="Name" value="{{$designation['Name']??old('Name')}}" autocomplete="off" class="form-control"/>
                            </div>
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            <a href="{{url('designationindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
