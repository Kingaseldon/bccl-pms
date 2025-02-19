@extends('master')
@section('page-title','Change Password')
@section('page-header','Change Password')
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-4 card dashboard-product">
                        {{Form::open(['url'=>'postupdatepassword','id'=>'change-pw-form'])}}
                            <div class="form-group">
                                <label for="new-pw-1">Enter your New Password</label>
                                <input type="password" id="new-pw-1" required="required" step="1" autocomplete="off" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="new-pw">Confirm New Password</label>
                                <input type="password" id="new-pw" name="NewPassword" required="required" autocomplete="off" class="form-control"/>
                            </div>
                            <button type="submit" class="dont-disable btn btn-primary" id="pw-match-check">Change</button>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop