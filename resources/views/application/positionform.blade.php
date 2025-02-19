@extends('master')
@section('page-title',$update?'Update Evaluation Criteria':'Add Evaluation Criteria')
@section('page-header',$update?'Update Evaluation Criteria':'Add Evaluation Criteria')
@section('action-button')
    @parent
    <a href="{{url('positionindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
                        {{Form::open(['url'=>'saveposition'])}}
                            {{Form::hidden('Id',$position['Id']??old('Id'))}}
                            <div class="form-group">
                                <label for="GradeStepId">Grade </label>
                                <select name="GradeStepId" id="GradeStepId" class="form-control select2">
                                    <option value="">--</option>
                                    @foreach($grades as $grade)
                                        <option value="{{$grade->Id}}" @if($grade->Id == ($position['GradeStepId']??old('GradeStepId'))) selected="selected"@endif>{{$grade->Name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="GradeId">Group <span class="required">*</span></label>
                                <select name="SupervisorId" id="SupervisorId" required="required" class="form-control select2">
                                    <option value="">--SELECT--</option>
                                    @foreach($supervisorLevels as $supervisorLevel)
                                        <option value="{{$supervisorLevel->Id}}" @if($supervisorLevel->Id == ($position['SupervisorId']??old('SupervisorId'))) selected="selected"@endif>{{$supervisorLevel->Name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{--<div class="form-group">--}}
                                {{--<label for="Position">Position <span class="required">*</span></label>--}}
                                {{--<input type="text" autocomplete="off" id="Position" required="required" name="Name" value="{{isset($position['Name'])?$position['Name']:old('Name')}}" autocomplete="off" class="form-control"/>--}}
                            {{--</div>--}}
                            <div class="form-group">
                                <label for="Dept">Department (select multiple if needed) <span class="required">*</span></label>
                                <select id="Dept" name="DepartmentId[]" required="required" class="form-control select2" multiple="multiple">
                                    @foreach($departments as $department)
                                        <option value="{{$department->Id}}" @if(in_array($department->Id,$positionDepartmentMaps))selected="selected"@endif >{{$department->Name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{--<div class="form-group">--}}
                                {{--<label for="DisplayOrder">Display Priority <span class="required">*</span></label>--}}
                                {{--<select id="DisplayOrder" name="DisplayOrder" required="required" class="form-control select2">--}}
                                    {{--@for($i=1; $i<=20; $i++)--}}
                                        {{--<option value="{{$i}}" @if($i==isset($position['DisplayOrder'])?$position['DisplayOrder']:old('DisplayOrder'))selected="selected"@endif>{{$i}}</option>--}}
                                    {{--@endfor--}}
                                {{--</select>--}}
                            {{--</div>--}}
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            <a href="{{url('positionindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
