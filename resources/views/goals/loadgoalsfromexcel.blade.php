<?php $count=0; $total = 0; ?>
@foreach($sheetData as $detail)
    @if((bool)$detail[0] && (bool)$detail[1])
        <?php $randomKey = randomString(); $nameVar = ($type == 1) ? "goaldetailonm":"goaldetailpna"; ?>
        <tr>
            <td class="text-center">
                <button type="button" class="delete-row"><i class="fa fa-minus"></i></button>
            </td>
            <td class="description">
                <input type="hidden" name="{{$nameVar}}[{{$randomKey}}][DisplayOrder]" value=""/>
                <input type="hidden" name="{{$nameVar}}[{{$randomKey}}][Id]" value=""/>
                <textarea style="width:100%;" name="{{$nameVar}}[{{$randomKey}}][Description]">{{$detail[0]}}</textarea>
            </td>
            <td class="text-center">
                <input type="number" class="goal-weightage text-right" min="1" step=".5" name="{{$nameVar}}[{{$randomKey}}][Weightage]" value="{{$detail[1]}}"/><?php $total += doubleval($detail[1]); ?>
            </td>
            <td class="text-center">
                <input type="text" name="{{$nameVar}}[{{$randomKey}}][Target]" value="{{$detail[2]}}"/>
            </td>
        </tr>
        <?php $count++; ?>
    @endif
@endforeach
@if($count == 0)
    <?php $randomKey = randomString(); $nameVar = ($type == 1) ? "goaldetailonm":"goaldetailpna"; ?>
    <tr>
        <td class="text-center">
            <button type="button" class="delete-row"><i class="fa fa-minus"></i></button>
        </td>
        <td class="description">
            <input type="hidden" name="{{$nameVar}}[{{$randomKey}}][DisplayOrder]" value=""/>
            <input type="hidden" name="{{$nameVar}}[{{$randomKey}}][Id]" value=""/>
            <textarea style="width:100%;" name="{{$nameVar}}[{{$randomKey}}][Description]"></textarea>
        </td>
        <td class="text-center">
            <input type="number" class="goal-weightage text-right" min="1" step=".5" name="{{$nameVar}}[{{$randomKey}}][Weightage]" value=""/>
        </td>
        <td class="text-center">
            <input type="text" name="{{$nameVar}}[{{$randomKey}}][Target]" value=""/>
        </td>
    </tr>
@endif
<tr class="dont-clone">
    <td class="text-center">
        <button type="button" class="add-new-row"><i class="fa fa-plus"></i></button>
    </td>
    <td class="text-right"><strong>Total</strong></td>
    <td>
        <input type="text" value="{{number_format($total,2)}}" autocomplete="off" class="form-control input-xs goal-total text-right" disabled="disabled"/>
    </td>
    <td></td>
</tr>