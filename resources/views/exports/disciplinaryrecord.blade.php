<table>
    <thead>
    <tr>
        <th>Sl#</th>
        <th>Name</th>
        <th>CID</th>
        <th>Designation</th>
        <th>Department</th>
        <th>Offence</th>
        <th>Action Taken By</th>
        <th>Action Taken</th>
        <th>Action Taken On</th>
        <th class="text-center" style="width:200px;">Actions</th>
    </tr>
    </thead>
    <tbody>
        <?php $slNo=1; ?>
        @forelse($disciplinaryRecords as $disciplinary)
            <tr>
                <td>{{$slNo++}}. </td>
                <td>{{$disciplinary->Employee}}</td>
                <td>{{$disciplinary->CIDNo}}</td>
                <td>{{$disciplinary->SavedDesignation}}</td>
                <td>{{$disciplinary->Department}}</td>
                <td>{{$disciplinary->Record}}</td>
                <td>{!! $disciplinary->ActionTakenBy !!}</td>
                <td>{{$disciplinary->RecordDescription}}</td>
                <td>{{convertDateToClientFormat($disciplinary->RecordDate)}}</td>
                <td class="text-center">
                    <a class="btn btn-xs btn-primary editconfirm" href="{{url('disciplinaryinput',[$disciplinary->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('disciplinarydelete',[$disciplinary->Id])}}"><i class="fa fa-times"></i> Delete</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center"><strong>No data to display.</strong></td>
            </tr>
        @endforelse
    </tbody>
</table>
