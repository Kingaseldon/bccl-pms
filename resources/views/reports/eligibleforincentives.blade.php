@extends('master')
@section('page-title','Employees Eligible for Incentives')
@section('page-header',"Employees Eligible for Incentives")

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 20px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{Request::url()}}" method="GET" id="form-daterestriction">
                            <div class="row">
                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2 fetch-employee-on-dept" id="filter-section">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == app('request')->input('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->ShortName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-12 col-sm-4">
                                    <br>
                                    <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                    <a href="{{URL::to('eligibleforincentivereport')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a> &nbsp;
                                </div>
                            </div>

                        </form>
                        <div class="row">

                            <div class="col-md-12">
                                <br>
                                <?php $var = "Round of Last Reward"; ?>
                                <h6 style="text-decoration: none;">Eligible for Meritorious Promotion</h6>
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Sl #</th>
                                                <th>Employee</th>
                                                <th>Designation</th>
                                                <th>Grade Step</th>
                                                <th>Last Reward in PMS</th>
                                                <th>Requirement for Incentive</th>
                                                <th>Achievement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $slNo=1; ?>
                                            @forelse($outstandingEligible as $data)
                                                <tr>
                                                    <td>{{$slNo++}}</td>
                                                    <td><a href="{{url('viewprofile')}}/{{$data->Id}}" target="_blank">{{$data->Employee}}</a><br>EmpId: {{$data->EmpId}}</td>
                                                    <td>{{$data->Designation}} <br> {{$data->Section}} ({{$data->Department}})</td>
                                                    <td>{{$data->GradeStep}}</td>
                                                    <td>{{$data->$var ? "Round ".$data->$var: ""}}</td>
                                                    <td><em>{{$data->Requirement}} O</em></td>
                                                    <td>{{$data->Achieved}} O <button type="button" class="btn btn-primary fetch-pms-history btn-xs" data-id="{{$data->Id}}"><i class="fa fa-eye" ></i></button></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">No data to display!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <hr style="border-top: 2px solid #fff;">
                                <h6 style="text-decoration: none;">Eligible for Meritorious Increment</h6>
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Sl #</th>
                                            <th>Employee</th>
                                            <th>Designation</th>
                                            <th>Grade Step</th>
                                            <th>Last Reward in PMS</th>
                                            <th>Requirement for Incentive</th>
                                            <th>Achievement</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $slNo=1; ?>
                                        @forelse($outstandingAndGoodEligible as $data)
                                            <tr>
                                                <td>{{$slNo++}}</td>
                                                <td><a href="{{url('viewprofile')}}/{{$data->Id}}" target="_blank">{{$data->Employee}}</a><br>EmpId: {{$data->EmpId}}</td>
                                                <td>{{$data->Designation}} <br> {{$data->Section}} ({{$data->Department}})</td>
                                                <td>{{$data->GradeStep}}</td>
                                                <td>{{$data->$var ? "Round ".$data->$var: ""}}</td>
                                                <td><em>{{$data->Requirement}} G and O</em></td>
                                                <td>{{$data->Achieved}} G and O <button type="button" class="btn btn-primary fetch-pms-history btn-xs" data-id="{{$data->Id}}"><i class="fa fa-eye" ></i></button></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No data to display!</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <?php $var = "Round of Last Action"; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="pms-history">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">PMS Score History</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php //dd(count($dataArraySuper)); ?>
@endsection
