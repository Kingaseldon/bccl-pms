@extends('master')
@section('page-title', 'Employees')
@section('page-header', 'Manage Employees')
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
                                        <a href="{{ url('employeeinput') }}" class="btn btn-success btn-xs"><i
                                                class="fa fa-plus"></i> Add</a>
                                        <br><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of
                                    filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{ Request::url() }}" method="GET">
                            <div class="row">
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2" id="DepartmentId">
                                            <option value="">All</option>
                                            @foreach ($departments as $department)
                                                <option @if ($department->Id == app('request')->input('DepartmentId')) selected="selected" @endif
                                                    value="{{ $department->Id }}">{{ $department->Name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label for="DesignationLocation" class="control-label">Designation</label>
                                        <select name="DesignationId" class="form-control select2" id="DesignationLocation">
                                            <option value="">All</option>
                                            @foreach ($designationLocations as $designationLocation)
                                                <option data-deptids='[{{ $designationLocation->DepartmentIds }}]'
                                                    @if ($designationLocation->Id == app('request')->input('DesignationId')) selected="selected" @endif
                                                    value="{{ $designationLocation->Id }}">{{ $designationLocation->Name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="Name" class="control-label">Employee Name</label>
                                        <input type="text" autocomplete="off" name="Name"
                                            value="{{ app('request')->input('Name') }}" id="Name"
                                            class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="EmpId" class="control-label">Employee Id</label>
                                        <input type="text" autocomplete="off" name="EmpId"
                                            value="{{ app('request')->input('EmpId') }}" id="EmpId"
                                            class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="GradeStepId" class="control-label">Grade/Step</label>
                                        <select name="GradeStepId" class="form-control select2" id="GradeStepId">
                                            <option value="">All</option>
                                            @foreach ($gradeSteps as $gradeStep)
                                                <option @if ($gradeStep->Id == app('request')->input('GradeStepId')) selected="selected" @endif
                                                    value="{{ $gradeStep->Id }}">{{ $gradeStep->Name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted" />
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-4 col-8">
                                <div class="row">
                                    <button type="submit" style="" class="btn btn-primary"><i
                                            class="fa fa-search"></i> Search</button> &nbsp;
                                    <a href="{{ url('employeeindex') }}" style="" class="btn btn-danger"><i
                                            class="fa fa-times"></i> Clear</a>
                                </div>
                            </div>
                        </form>
                        <div class="row">

                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered table-striped font-small">
                                        <thead>
                                            <tr>
                                                <th>Sl. #</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Extension</th>
                                                <th style="width:15%;">Designation</th>
                                                {{-- <th>Grade Step</th> --}}
                                                <th>Dept.</th>
                                                {{-- <th>Role</th> --}}
                                                <th>Status</th>
                                                <th class="text-center" style="width:32%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $params = app('request')->all();
                                        $append = '';
                                        foreach ($params as $key => $value):
                                            if (gettype($value) == 'array') {
                                                foreach ($value as $x => $y):
                                                    if ($append == ''):
                                                        $append .= '?';
                                                    else:
                                                        $append .= '&';
                                                    endif;
                                                    $append .= "$key" . '[]' . "=$y";
                                                endforeach;
                                            } else {
                                                if ($append == ''):
                                                    $append .= '?';
                                                else:
                                                    $append .= '&';
                                                endif;
                                                $append .= "$key=$value";
                                            }
                                        endforeach;
                                        $append = urlencode($append);
                                        ?>
                                        <?php $slNo = app('request')->has('page') ? (app('request')->input('page') - 1) * $perPage + 1 : 1; ?>
                                        @forelse($employees as $employee)
                                            <tr>
                                                <td>{{ $slNo++ }}. </td>
                                                <td><a href="{{ url('viewprofile', [$employee->Id]) }}"
                                                        target="_blank"><strong>{{ $employee->Name }}</strong></a>
                                                    <br><strong>CID:</strong> {{ $employee->CIDNo }}<br><strong>Emp
                                                        Id:</strong> {{ $employee->EmpId }}</td>
                                                <td>{{ $employee->Email }}</td>
                                                <td>{{ $employee->Extension }}</td>
                                                <td>{{ $employee->DesignationLocation }}</td>
                                                {{--                                                <td>{{$employee->Position}}</td> --}}
                                                <td>{{ $employee->Department }}</td>
                                                {{--                                                <td>{{$employee->Role}}</td> --}}
                                                <td>
                                                    @if ($employee->Status == 1)
                                                        Regular/Contract
                                                    @elseif($employee->Status == 2)
                                                        In-active
                                                    @elseif($employee->Status == 3)
                                                        Probation
                                                    @elseif($employee->Status == 4)
                                                        Suspended
                                                    @elseif($employee->Status == 5)
                                                        Terminated
                                                    @else
                                                        Resigned
                                                    @endif

                                                </td>
                                                <td class="text-center">
                                                    <a class="btn btn-inverse-warning btn-xs reset-password"
                                                        data-name="{{ $employee->Name }}" data-id="{{ $employee->Id }}"
                                                        href="#"><i class="fa fa-edit"></i> Reset
                                                        Password</a>&nbsp;&nbsp;
                                                    <a class="btn btn-xs btn-primary editconfirm"
                                                        href="{{ url('employeeinput', [$employee->Id]) }}?redirect=employeeindex{{ $append }}"><i
                                                            class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;
                                                    <a class="btn btn-danger btn-xs deleteconfirm"
                                                        href="{{ url('employeedelete', [$employee->Id]) }}"><i
                                                            class="fa fa-times"></i> Delete</a>&nbsp;&nbsp;
                                                    @if (Auth::user()->Extension == 1234)
                                                        <a class="btn btn-inverse-primary btn-xs"
                                                            href="{{ url('loginasemployee', [$employee->Id]) }}"><i
                                                                class="fa fa-sign-in"></i> Login As</a>&nbsp;&nbsp;
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center"><strong>No data to
                                                        display.</strong></td>
                                            </tr>
                                        @endforelse
                                    </table>
                                    {{ $employees->appends(app('request')->except('page'))->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
