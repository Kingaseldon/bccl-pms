@extends('master')
@section('page-title',"Goals/Targets for ".date('Y'))
@section('page-header',"Goals/Targets for ".date('Y'))
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
                                {{--                                @if(Auth::user()->PositionId == CONST_POSITION_HOD)--}}
                                {{--                                    <a href="{{url('setgoal',[Auth::user()->Id])}}" class="btn btn-xs btn-warning">Set My Own Goal</a>--}}
                                {{--                                    <br><br>--}}
                                {{--                                @endif--}}

                                @if($type == 1)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                                            <thead>
                                            <tr>
                                                <th style="width:20px;">Sl.#</th>
                                                <th>Employee</th>
                                                <th>Grade</th>
                                                <th class="text-center">Targets and Goals</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $slNo = 1; ?>
                                            @forelse($employees as $employee)
                                                <tr>
                                                    <td>{{$slNo++}}</td>
                                                    <td>{{$employee->Name}} - ({{$employee->Designation}})</td>
                                                    <td>{{$employee->Position}}</td>
                                                    <td class="text-center" style="font-weight:bold;padding: 0.45rem 0.4rem 0.4rem 0.4rem;">
                                                        @foreach($roundsForThisYear as $round)
                                                            @if((int)$round->IsSecondRound === 1)
                                                                @if($employee->Goal2DefinitionId)
                                                                    <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-warning @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-edit"></i> Edit - {{$round->Round}}</a> &nbsp;&nbsp;&nbsp;
                                                                @else
                                                                    <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-success @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-eye"></i> Set - {{$round->Round}}</a> &nbsp;&nbsp;
                                                                    &nbsp;
                                                                @endif
                                                            @else
                                                                @if($employee->Goal1DefinitionId)
                                                                    <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-warning @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-edit"></i> Edit - {{$round->Round}}</a>
                                                                @else
                                                                    <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-success @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-eye"></i> Set - {{$round->Round}}</a>

                                                                @endif
                                                            @endif
                                                        @endforeach
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
                                                            <th>Grade</th>
                                                            <th class="text-center">Targets and Goals</th>
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
                                                                <td class="text-center" style="font-weight:bold;padding: 0.45rem 0.4rem 0.4rem 0.4rem;">
                                                                    @foreach($roundsForThisYear as $round)
                                                                        @if((int)$round->IsSecondRound === 1)
                                                                            @if($employee->Goal2DefinitionId)
                                                                                <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-warning @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-edit"></i> Edit - {{$round->Round}}</a> &nbsp;&nbsp;&nbsp;
                                                                            @else
                                                                                <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-success @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-eye"></i> Set - {{$round->Round}}</a> &nbsp;&nbsp;
                                                                                &nbsp;
                                                                            @endif
                                                                        @else
                                                                            @if($employee->Goal1DefinitionId)
                                                                                <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-warning @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-edit"></i> Edit - {{$round->Round}}</a>
                                                                            @else
                                                                                <a data-id="{{$round->Id}}" href="{{url('setgoal',[$employee->Id,$round->Id])}}" class="btn btn-xs btn-success @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-eye"></i> Set - {{$round->Round}}</a>

                                                                            @endif
                                                                        @endif
                                                                    @endforeach
                                                                    {{--@if((bool)$employee->GoalDefinitionId)
                                                                        <a href="{{url('setgoal',[$employee->Id])}}" class="@if(!$notWithinPMSPeriod){{""}}@endif btn btn-xs btn-warning @if((bool)$employee->PMSSubmissionId){{"disabled"}}@endif"><i class="fa fa-edit"></i> Edit - {{$currentRound}}</a>
                                                                    @else
                                                                        <a href="{{url('setgoal',[$employee->Id])}}" class="@if(!$notWithinPMSPeriod){{""}}@endif btn btn-xs btn-success"><i class="fa fa-eye"></i> Set - {{$currentRound}} &nbsp;&nbsp;</a>
                                                                    @endif--}}
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

