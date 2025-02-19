@extends('master')
@section('page-title','Resubmit PMS')
@section('page-header','Resubmit PMS')
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-8 card dashboard-product">
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
                        {{Form::open(['url'=>'resubmitpms','files'=>true])}}
                        {{Form::hidden('Id',$id)}}
                                @if((bool)$filePath)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="{{url('filedownload')}}?file={{$filePath}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Self Rated File</a>
                                            <br>
                                            <br>
                                        </div>
                                    </div>
                                @endif
                            @if((bool)$filePath3)
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="{{url('filedownload')}}?file={{$filePath3}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 1 Rated File</a>
                                        <br>
                                        <br>
                                    </div>
                                </div>
                            @endif
                            @if((bool)$filePath4)
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="{{url('filedownload')}}?file={{$filePath4}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 2 Rated File</a>
                                        <br>
                                    </div>
                                </div>
                            @endif
                            @if((bool)$filePath2)
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="{{url('filedownload')}}?file={{$filePath2}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Supporting Document</a>
                                        <br>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ExcelApplicant">Goals/Targets Re-Upload file [5MB Max]</label>
                                        <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" id="ExcelApplicant" name="File" value="{{$position['Name']??old('Name')}}" autocomplete="off" class="form-control file-xs"/>
                                    </div>
                                </div>
                                <div class="col-md-6 offset-2">
                                    <div class="form-group">
                                        <label for="SupportingDoc">Re-upload Supporting document for Additional Achievement [5MB Max]</label>
                                        <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" id="SupportingDoc" name="File2" value="{{$position['Name']??old('Name')}}" autocomplete="off" class="form-control file-xs"/>
                                    </div>
                                </div>
                            </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed" id="calc-total">
                                <thead>
                                <tr>
                                    <th style="width:40px;">Sl #</th>
                                    <th>Assessment Area</th>
                                    <th class="text-center">Weight (%)</th>
                                    <th class="text-center" style="width:16%;">Self Rating</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $count=1; $sum = 0; ?>
                                @foreach($details as $assessmentArea)
                                    <?php $randomKey = randomString(); ?>
                                    <tr>
                                        <td class="text-center">{{$count}}.</td>
                                        <td class="description">
                                            <input type="hidden" name="pmssubmissiondetail[{{$randomKey}}][Id]" value="{{$assessmentArea->Id}}"/>
                                            {{$assessmentArea->AssessmentArea}}
                                        </td>
                                        <td class="text-center">
                                            {{$assessmentArea->Weightage}}
                                        </td>
                                        <td>
                                            <input type="text" onkeydown="return event.keyCode !== 69" name="pmssubmissiondetail[{{$randomKey}}][SelfRating]"
                                                   @if($count == 1)
                                                   readonly="readonly" value="{{$goalAchievementScore}}"
                                                        <?php $sum+=doubleval($goalAchievementScore);?>
                                                   @else
                                                   value="{{$assessmentArea->SelfRating}}"
                                                        <?php $sum+=doubleval($assessmentArea->SelfRating);?>
                                                   @endif
                                                    min="0" max="{{$assessmentArea->Weightage}}"
                                                   step="any" required="required" class="form-control
                                                   @if(Auth::user()->PositionId <> CONST_POSITION_HOD && $count == 1 && Auth::user()->DepartmentId == 7)
                                                        {{"text-center"}}
                                                   @endif input-xs figure"/>

                                        </td>
                                    </tr>
                                    <?php $count++; ?>
                                @endforeach
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                                    <td>
                                        <input type="text" autocomplete="off" value="{{number_format($sum,2)}}" class="form-control input-xs" id="figure-total" disabled="disabled"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="well">
                            <h6>History of Evaluation</h6>
                            {!! $assessmentArea->History !!}
                        </div>
                        <button type="button" id="save-appraisee" class="btn btn-primary">Save as Draft</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{url('trackpms')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

