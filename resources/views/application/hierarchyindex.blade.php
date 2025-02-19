@extends('master')
@section('page-title','Appraisal Structure')
@section('page-header',"Appraisal Structure")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-10">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{Request::url()}}" method="GET">
                            <div class="row">
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2" id="DepartmentId">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == app('request')->input('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label for="DesignationLocation" class="control-label">Designation</label>
                                        <select name="DesignationId" class="form-control select2" id="DesignationLocation">
                                            <option value="">All</option>
                                            @foreach($designationLocations as $designationLocation)
                                                <option data-deptids='[{{$designationLocation->DepartmentIds}}]' @if($designationLocation->Id == app('request')->input('DesignationId'))selected="selected"@endif value="{{$designationLocation->Id}}">{{$designationLocation->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="Name" class="control-label">Name</label>
                                        <input type="text" autocomplete="off" name="Name" value="{{app('request')->input('Name')}}" id="Name" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="EmpId" class="control-label">Employee Id</label>
                                        <input type="text" autocomplete="off" name="EmpId" value="{{app('request')->input('EmpId')}}" id="EmpId" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-3 col-md-4 col-sm-4 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('hierarchyindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
                                            <br><br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed table-bordered font-small">
                                        <thead>
                                            <tr>
                                                <th>Sl#</th>
                                                <th>Employee</th>
                                                <th>Designation</th>
                                                <th>Department</th>
                                                <th>Reports to (Level 1)</th>
                                                <th>Reports to (Level 2)</th>
                                                <th class="text-center" style="width:22%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <?php
                                            $params = app('request')->all();
                                            $append = "";
                                            foreach($params as $key=>$value):
                                                if(gettype($value)=='array'){
                                                    foreach($value as $x=>$y):
                                                        if($append == ''):
                                                            $append.="?";
                                                        else:
                                                            $append.="&";
                                                        endif;
                                                        $append.="$key"."[]"."=$y";
                                                    endforeach;
                                                }else{
                                                    if($append == ''):
                                                        $append.="?";
                                                    else:
                                                        $append.="&";
                                                    endif;
                                                    $append.="$key=$value";
                                                }

                                            endforeach;
                                            $append = urlencode($append);
                                        ?>
                                        <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                        @forelse($hierarchies as $hierarchy)
                                            <tr>
                                                <td>{{$slNo++}}. </td>
                                                <td><a href="{{url('viewprofile',[$hierarchy->Id])}}" target="_blank"><strong>{{$hierarchy->Name}}</strong></a> <br><strong>CID:</strong> {{$hierarchy->CIDNo}}<br><strong>Emp Id:</strong> {{$hierarchy->EmpId}}</td>
                                                <td>{{$hierarchy->Designation}}</td>
                                                <td>{{$hierarchy->Department}}</td>
                                                <td>{!! $hierarchy->Level1 !!}</td>
                                                <td>{!! $hierarchy->Level2 !!}</td>
                                                <td class="text-center">
                                                    <a class="btn btn-xs btn-warning editconfirm" href="{{url('hierarchyinput',[$hierarchy->Id])}}?redirect=hierarchyindex{{$append}}"><i class="fa fa-edit"></i> {{(bool)$hierarchy->Level1?"Update":"Set"}} Appraisal Structure</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center"><strong>No data to display.</strong></td>
                                            </tr>
                                        @endforelse
                                    </table>
                                    {{$hierarchies->appends(app('request')->except('page'))->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
