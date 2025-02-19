@extends('master')
@section('page-title','My Performance Goals')
@section('page-header','My Performance Goals')
@section('pagestyles')
    {{Html::style("assets/plugins/lightcase/css/lightcase.css")}}
@endsection
@section('pagescripts')
    {{Html::script("assets/plugins/lightcase/js/lightcase.js")}}
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12 col-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-12 card dashboard-product">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(Session::has('message'))
                            <h6><i class="fa fa-times-circle" style="color:red"></i> {!!Session::get('message')!!}</h6>
                        @endif
                        @if(!$inaccessible)
                            @if(count($onmTargets)>0 || count($goalTargets)>0)
                                {{Form::open(['url'=>'savegoalscore'])}}
                                {{Form::hidden('Id',(bool)$goalId?$goalId:'')}}
                                {{Form::hidden('IsSelf',1)}}
                                <br>
                                <h4>Work Plan</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                <th style="width:47%">Description (Goal)</th>
                                                <th class="text-center" style="width:9%;">Weightage (W)</th>
                                                <th class="text-center" style="width:15%;">Target (T)</th>
                                                <th class="text-center" style="width:7%;">Self Score</th>
                                                <th style="width:22%">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count=1; $total = $selfScoreTotal = 0; ?>
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
                                                    <input type="number" class="text-right goal-weightage" min="0" step="any" max="{{$detail->Weightage}}" style="width:100%" required="required" name="goaldetailpna[{{$randomKey}}][SelfScore]" value="{{$detail->SelfScore}}"/><?php $selfScoreTotal += doubleval($detail->SelfScore); ?>
                                                </td>
                                                <td class="text-center">
                                                    <textarea style="width:100%;" rows="2" name="goaldetailpna[{{$randomKey}}][SelfRemarks]">{{$detail->SelfRemarks}}</textarea>
                                                </td>
                                            </tr>
                                            <?php $count++; ?>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No Projects & Activities defined.</td>
                                            </tr>
                                        @endforelse
                                        @if(count($goalTargets))
                                        <tr class="dont-clone">
                                            <td class="text-right"><strong>Total</strong></td>
                                            <td class="text-right">
                                                {{number_format($total,2)}}
                                            </td>
                                            <td></td>
                                            <td>
                                                <input type="text" value="{{number_format($selfScoreTotal,2)}}" autocomplete="off" class="form-control input-xs goal-total text-right" disabled="disabled"/>
                                            </td>
                                            <td></td>
                                        </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" id="save-goals" class="btn btn-primary">Save</button>
                                <a href="{{url('mypmsgoal')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                                {{Form::close()}}
                            @else
                                <strong>Your Goal Achievements have not been defined by your Appraiser.</strong>
                            @endif
                        @else
                            <h4>Work Plan</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                    <tr>
                                        <th style="width:47%">Description (Goal)</th>
                                        <th class="text-center" style="width:9%;">Weightage (W)</th>
                                        <th class="text-center" style="width:15%;">Target (T)</th>
                                        <th class="text-center" style="width:7%;">Self Score</th>
                                        <th style="width:22%">Remarks</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $count=1; $total = $selfScoreTotal = 0; ?>
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
                                            <td class="text-right">
                                                {{$detail->SelfScore}}<?php $selfScoreTotal += doubleval($detail->SelfScore); ?>
                                            </td>
                                            <td class="text-center">
                                                {{$detail->SelfRemarks}}
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No Projects & Activities defined.</td>
                                        </tr>
                                    @endforelse
                                    @if(count($goalTargets))
                                    <tr class="dont-clone">
                                        <td class="text-right"><strong>Total</strong></td>
                                        <td class="text-right">
                                            {{number_format($total,2)}}
                                        </td>
                                        <td></td>
                                        <td class="text-right">
                                            {{number_format($selfScoreTotal,2)}}
                                        </td>
                                        <td></td>
                                    </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
