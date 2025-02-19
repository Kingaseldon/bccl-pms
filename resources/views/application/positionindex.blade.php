@extends('master')
@section('page-title','Evaluation Criteria')
@section('page-header',"Manage Evaluation Criteria")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{url('positioninput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                <table class="table table-striped table-condensed table-bordered font-small">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Sl#</th>
                                            <th style="width:22%;">For Group</th>
                                            {{--<th style="width:2%;">Display Priority</th>--}}
                                            <th>Set Assessment Areas and Weightage (Green if already set)</th>
                                            <th class="text-center" style="width:16%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <?php $slNo=1; ?>
                                    @forelse($positions as $position)
                                        <tr>
                                            <td class="text-center">{{$slNo++}}. </td>
                                            <td>{{$position->PositionName}}</td>
{{--                                            <td class="text-center">{{$position->DisplayOrder}}</td>--}}
                                            <td>
                                                <?php
                                                    $departments = $position->Departments;
                                                    $departmentArray = explode('|',$departments);
                                                ?>
                                                @if((bool)$departments)
                                                    <?php $count = 1; ?>
                                                    @foreach($departmentArray as $department)
                                                        <?php $departmentDetail = explode('_',$department); ?>
                                                            @if($count>1) | @endif <a style="margin-bottom:3px;" href="@if($position->Id != CONST_POSITION_MD){{url('criteriainput',[trim($departmentDetail[0]),$position->Id])}}@else{{"#"}}@endif" class="btn-xs @if($position->Id != CONST_POSITION_MD){{"editconfirm"}}@endif btn btn-{{((bool)$departmentDetail[2])?'inverse-success':'inverse-warning'}}">@if($position->Id != CONST_POSITION_MD)<i class="fa fa-edit"></i>@endif {{$departmentDetail[1]}}</a>
                                                        <?php $count++; ?>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-primary editconfirm" href="{{url('positioninput',[$position->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;@if(!in_array($position->Id,[CONST_POSITION_HOD,CONST_POSITION_HOS]))<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('positiondelete',[$position->Id])}}"><i class="fa fa-times"></i> Delete</a>@endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center"><strong>No data to display.</strong></td>
                                        </tr>
                                    @endforelse
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="criteria-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Set criteria for <span id="position-name"></span> of <span id="dept-name"></span></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="criteria-modal-form">
                    Modal body..
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

