@extends('master')
@section('page-title','Change Password')
@section('page-header','Change Password')
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-4 card dashboard-product">
                        @if(Session::has('message'))
                            <h6 class="no-decoration"><i class="fa fa-times-circle" style="color:red"></i> {!!Session::get('message')!!}</h6>
                        @endif
                        {{Form::open(['url'=>'postcheckpassword'])}}
                            <div class="form-group">
                                <label for="old-pw">Enter your Old Password</label>
                                <input type="password" required="required" id="old-pw" name="OldPassword" autocomplete="off" class="form-control"/>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop