@extends('master')
@section('page-title',$update?'Update Criteria':'Set Criteria')
@section('page-header',$update?'Update Criteria':'Set Criteria')
@section('action-button')
    @parent
    <a href="{{url('positionindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
@endsection
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
                        <h6 class="no-decoration">{!!$update?'Update Criteria for <em>'.$positionName.'</em> of '.$departmentName:'Set Criteria for <em>'.$positionName.'</em> of '.$departmentName!!}</h6>
                            <br>
                        <h6 class="no-decoration">Rating Weightage [Total 100]</h6>
                        @if(Session::has('message'))
                            <h6><i class="fa fa-times-circle" style="color:red"></i> {!!Session::get('message')!!}</h6>

                        @endif
                        {{Form::open(['url'=>'savecriteria','id'=>'set-criteria-form'])}}
                            <div class="row">

                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    {{Form::hidden('Id',$rating[0]->Id??old('Id'))}}
                                    {{Form::hidden('PositionDepartmentId',$positionDepartmentId)}}
                                    <div class="form-group">
                                        <label for="WeightageForLevel1">Weightage For Level 1 (%) <span class="required">*</span></label>
                                        <input type="number" onkeydown="return event.keyCode !== 69" step="any" min="0" max="100" id="WeightageForLevel1" required="required" name="WeightageForLevel1" value="{{$rating[0]->WeightageForLevel1??old('WeightageForLevel1')}}" autocomplete="off" class="form-control"/>
                                    </div>

                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="WeightageForLevel2">Weightage For Level 2 (%)</label>
                                        <input type="number" onkeydown="return event.keyCode !== 69" step="any" min="0" max="100" id="WeightageForLevel2" name="WeightageForLevel2" value="{{$rating[0]->WeightageForLevel2??old('WeightageForLevel2')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <label for="Level2CriteriaType">Level 2 Evaluates </label>
                                    <div class="form-check-inline" style="margin-top:6px;">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" value="1" @if(!isset($rating[0]->Level2CriteriaType) || $rating[0]->Level2CriteriaType == 1)checked="checked"@endif name="Level2CriteriaType">Only Qualitative Areas
                                        </label>
                                    </div>
                                    <div class="form-check-inline" style="margin-top:6px;">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" value="2" @if(isset($rating[0]->Level2CriteriaType) && $rating[0]->Level2CriteriaType == 2)checked="checked"@endif name="Level2CriteriaType">All Areas
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <h6 class="no-decoration">Assessment areas and Weightage [Total 100]</h6>
                            <div class="table-responsive">
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                    <tr>
                                        <th style="width:20px"></th>
                                        <th>Assessment Area</th>
                                        <th style="width:110px">Weightage (%)</th>
                                        <th style="width:150px">Is Qualitative Score</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($ratingCriteria as $criteria)
                                        <?php $randomKey = randomString(); ?>
                                        <tr>
                                            <td class="text-center">
                                                <button type="button" class="delete-row"><i class="fa fa-minus"></i></button>
                                            </td>
                                            <td>
                                                <input type="text" autocomplete="off" value="{{$criteria->Description}}" name="criteria[{{$randomKey}}][Description]" required="required" class="form-control input-xs"/>
                                            </td>
                                            <td>
                                                <input type="number" onkeydown="return event.keyCode !== 69" min="1" max="100" step="any" value="{{$criteria->Weightage}}" style="width:70px" name="criteria[{{$randomKey}}][Weightage]" required="required" class="form-control input-xs assessment-weightage"/>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" @if($criteria->ApplicableToLevel2)checked="checked"@endif name="criteria[{{$randomKey}}][ApplicableToLevel2]" value="1"/>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="dont-clone">
                                        <td class="text-center">
                                            <button type="button" class="add-new-row"><i class="fa fa-plus"></i></button>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div><div class="clearfix"></div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" id="validate-criteria" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                                    <a href="{{url('positionindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                                </div>
                            </div>
                            {{Form::close()}}

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
