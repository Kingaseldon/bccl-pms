@extends('master')
@section('page-title',$update?'Update Grade / Steps':'Add Grade / Steps')
@section('page-header',$update?'Update Grade / Steps':'Add Grade / Steps')
@section('action-button')
    @parent
    <a href="{{url('gradestepindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
                        {{Form::open(['url'=>'savegradestep'])}}
                            {{Form::hidden('Id',$gradestep['Id']??old('Id'))}}
                            <div class="form-group">
                                <label for="GradeId">Grade <span class="required">*</span></label>
                                <select name="GradeId" required="required" id="GradeId" class="form-control select2">
                                    <option value="">--SELECT--</option>
                                    @foreach($grades as $grade)
                                        <option value="{{$grade->Id}}" @if($grade->Id == ($gradestep['GradeId']??old('GradeId')))selected="selected"@endif >{{$grade->Name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Name">Name <span class="required">*</span></label>
                                <input type="text" id="Name" required="required" name="Name" value="{{$gradestep['Name']??old('Name')}}" autocomplete="off" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="PayScale">Pay Scale </label>
                                @if((int)$setting === 1)
                                    <input type="text" pattern="[0-9^,]{4,7} - [0-9^,]{3,5} - [0-9]{4,7}" title="Should be in the format Minimum - Increment - Maximum (for eg, 19288 - 335 - 23000)" id="PayScale" name="PayScale" value="{{isset($gradestep['PayScale'])?$gradestep['PayScale']:old('PayScale')}}" autocomplete="off" class="form-control"/>
                                @else
                                    <input type="text" pattern="[0-9^,]{4,7} - [0-9^,]{3,5} - [0-9]{4,7} - [0-9^,]{3,5} - [0-9]{4,7}" title="Should be in the format Minimum - Increment - Middle - Increment - Maximum (for eg, 19288 - 335 - 21200 - 440 - 28000)" id="PayScale" name="PayScale" value="{{isset($gradestep['PayScale'])?$gradestep['PayScale']:old('PayScale')}}" autocomplete="off" class="form-control"/>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="Status">Status <span class="required">*</span></label>
                                <select name="Status" required="required" id="Status" class="form-control">
                                    <option value="1" @if(isset($gradestep['Status']) && $gradestep['Status'] == 1)selected="selected"@endif>Active</option>
                                    <option value="0" @if(isset($gradestep['Status']) && $gradestep['Status'] === 0)selected="selected"@endif>In-Active</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            <a href="{{url('gradestepindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
