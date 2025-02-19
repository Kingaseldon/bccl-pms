<table>
    <thead>
        <tr>
            <th>Sl. No.</th>
            <th>Employee</th>
            <th>Extension</th>
        </tr>
    </thead>
    <tbody>
        <?php $slNo = 1; ?>
        @foreach($employees as $employee)
            <tr>
                <td>{{$slNo++}}</td>
                <td>{{$employee->Name}}</td>
                <td>{{$employee->Extension}}</td>
            </tr>
        @endforeach
    </tbody>
</table>