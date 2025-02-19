@extends('master')
@section('page-title','View Subordinate Details')
@section('page-header',"View Subordinate Details")
@section('pagescripts')
    <script>
        $(function(){
            $( "#accordion" ).accordion({
                collapsible: true,
                heightStyle: "content",
                active: false
            });
        });
    </script>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">

                        <div class="row">
                            <div class="col-md-12" data-type="{{$type}}">
                                @if($type == 1)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                                            <thead>
                                            <tr>
                                                <th style="width:20px;">Sl.#</th>
                                                <th>Employee</th>
                                                <th>Position</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $slNo = 1; ?>
                                            @forelse($employees as $employee)
                                                <tr>
                                                    <td>{{$slNo++}}</td>
                                                    <td>{{$employee->Name}} - ({{$employee->Designation}})</td>
                                                    <td>{{$employee->Position}}</td>
                                                    <td class="text-center">
                                                        <a href="{{url('empdetails',[$employee->Id])}}" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i> View</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4"><center>No data to display!</center></td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                <div id="accordion">
                                    @foreach($units as $unit)
                                        <h6><strong>{{$unit->Name}}</strong></h6>
                                        <div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-condensed large-padding" style="margin-bottom:0;">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:20px;">Sl.#</th>
                                                            <th>Employee</th>
                                                            @if($type == 3)<th>Section</th>@endif
                                                            <th>Evaluation Group</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $slNo = 1; ?>
                                                        @forelse($employees[$unit->Id] as $employee)
                                                            <tr>
                                                                <td>{{$slNo++}}</td>
                                                                <td>{{$employee->Name}} ({{$employee->Designation}})</td>
                                                                @if($type == 3)<td>{{$employee->Section}}</td>@endif
                                                                <td>{{$employee->Position}}</td>
                                                                <td class="text-center" style="padding: 0.45rem 0.4rem 0.4rem 0.4rem;">
                                                                    <a href="{{url('empdetails',[$employee->Id])}}" class="btn btn-xs btn-warning"><i class="fa fa-eye"></i> View</a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="{{$type==3?5:4}}"><center>No data to display!</center></td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>                                                   
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection