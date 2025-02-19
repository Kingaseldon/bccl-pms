<form method="POST" action="{{url('savegoalscore')}}">
    {{Form::token()}}
    <input type="hidden" name="Redirect" value="processpms/{{$id}}"/>
    <input type="hidden" name="PMSSubmissionId" value="{{$id}}"/>
<div class="modal-header">
    <h4 class="modal-title">Performance goals of {{$Employee}}</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <h4>Work Plan</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-condensed">
            <thead>
            <tr>
                <th style="width:30%">Description (Goal)</th>
                <th class="text-center" style="width:5%;">Weightage (W)</th>
                <th class="text-center" style="width:10%;">Target (T)</th>
                <th class="text-center" style="width:5%;">Self Score</th>
                <th style="width:11%">Self Remarks</th>
                <th class="text-center" style="width:5%;">Your Score</th>
                <th style="width:11%">Your Remarks</th>
            </tr>
            </thead>
            <tbody>
            <?php $count=1; $total = $selfScoreTotal = $level1ScoreTotal = 0; ?>
            @forelse($goalTargets as $detail)
                <?php $randomKey = randomString(); ?>
                <tr>
                    <td class="description">
                        <input type="hidden" name="goaldetailpna[{{$randomKey}}][Id]" value="{{$detail->Id}}"/>
                        {!!nl2br($detail->Description)!!}
                    </td>
                    <td class="text-right">
                        {{$detail->Weightage}}<?php $total += doubleval($detail->Weightage); ?>
                    </td>
                    <td class="text-center">
                        {{$detail->Target}}
                    </td>
                    <td class="text-center">
                        {{$detail->SelfScore}}<?php $selfScoreTotal += doubleval($detail->SelfScore); ?>
                    </td>
                    <td class="text-center">
                        {{$detail->SelfRemarks}}
                    </td>
                    <td class="text-center">
                        <input type="number" class="text-right goal-weightage" min="0" step="any" max="{{$detail->Weightage}}" style="width:100%;" required="required" name="goaldetailpna[{{$randomKey}}][Level1Score]" value="{{$detail->Level1Score}}"/><?php $level1ScoreTotal += doubleval($detail->Level1Score); ?>
                    </td>
                    <td class="text-center">
                        <textarea style="width:100%;" rows="2" name="goaldetailpna[{{$randomKey}}][Level1Remarks]">{{$detail->Level1Remarks}}</textarea>
                    </td>
                </tr>
                <?php $count++; ?>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No Projects & Activities defined.</td>
                </tr>
            @endforelse
            @if(count($goalTargets))
            <tr class="dont-clone">
                <td class="text-right"><strong>Total</strong></td>
                <td class="text-right">
                    {{number_format($total,2)}}
                </td>
                <td></td>
                <td class="text-center">
                    {{number_format($selfScoreTotal,2)}}
                </td>
                <td></td>
                <td>
                    <input type="text" value="{{number_format($level1ScoreTotal,2)}}" autocomplete="off" class="form-control input-xs goal-total text-right" disabled="disabled"/>
                </td>
                <td>

                </td>
            </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="submit" class="btn btn-success">Save Scores</button>

    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
</div>
</form>
