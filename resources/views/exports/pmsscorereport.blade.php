<table id="table-to-clone"class="sticky-columns table table-bordered table-striped table-hover table-condensed">
    <thead>
    <tr>
        <th>Employee</th>
        <th>DoA</th>
        <th>Duration of Service</th>
        <th>Basic Pay</th>
        <th>CID</th>
        {{--<th>Emp Id</th>--}}
        <th>Dept.</th>
        <th>Designation</th>
        <th>Work Location</th>
        <th>Grade</th>
        {{--<th>DoA</th>--}}
        {{--<th>Duration of Service</th>--}}
        {{--<th>Basic Pay</th>--}}
        <th>Section</th>
        @foreach($pmsPeriods as $pmsPeriod)
            @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                <th>{{date_format(date_create($pmsPeriod->StartDate),'M, Y')}}</th>
                <th>Result</th>
                <th>Remarks</th>
            @endif
        @endforeach
    </tr>
    </thead>
    @forelse($result as $data)
        <tr>
                <td>
                    @foreach($pmsPeriods as $pmsPeriod)
                        @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                            <?php $idVar = $pmsPeriod->Id." Id"; $id = $data->$idVar; $submissionIdVar = $pmsPeriod->Id." SubmissionId"; $submissionId = $data->$submissionIdVar; ?>
                        @endif
                    @endforeach
                    <strong>{{$data->Employee}}</strong>({{$data->EmpId}})
                </td>
                <td>
                    {{$data->DateOfAppointment}}
                </td>
                <td>
                    <?php
                    $dateOfRegularization = new DateTime($data->DateOfRegularization);
                    $now = new DateTime();
                    $diff = $dateOfRegularization->diff($now);
                    $diffYears = $diff->format("%y");
                    $diffMonths = $diff->format("%m");
                    ?>
                    @if($now > $dateOfRegularization)
                        @if($diffYears > 0)
                            @if($diffMonths == 0)
                                {!! $diff->format("%y Years") !!}
                            @else
                                {!! $diff->format("%y Years and %m Months") !!}
                            @endif
                        @else
                            @if($diffMonths > 0)
                                {{$diff->format("%m Months")}}
                            @else
                                N/A
                            @endif
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    {{$data->BasicPay}}
                </td>
            <td>{{$data->CIDNo}}</td>
            <td>{{$data->Department}}</td>
            <td>{{$data->Designation}}</td>
            <td>{{$data->JobLocation}}</td>
            <td>{{$data->GradeStep}}</td>
            <td>{{$data->Section}}</td>
            @foreach($pmsPeriods as $pmsPeriod)
                @if(in_array($pmsPeriod->Id,$pmsPeriodArray))
                    <?php $scoreVar = $pmsPeriod->Id." Score"; $resultVar = $pmsPeriod->Id." Result"; $remarksVar = $pmsPeriod->Id." Remarks"; ?>
                    <td>{{$data->$scoreVar}}</td>
                    <td>{{$data->$resultVar}}</td>
                    <td>{{$data->$remarksVar}}</td>
                @endif
            @endforeach
        </tr>
    @empty
        @if(app('request')->has('DepartmentId'))
            <tr><td class="headcol">&nbsp;</td><td colspan="90">No data to display</td></tr>
        @else
            <tr><td class="headcol">&nbsp;</td><td colspan="90">Please select a department and click search to view this report</td></tr>
        @endif

    @endforelse
</table>
