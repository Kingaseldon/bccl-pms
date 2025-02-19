@extends('master')
@section('page-title','Generate Office Order')
@section('page-header','Generate Office Order')
@section('action-button')
    @parent
{{--    <a href="{{url('designationindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>--}}
@endsection
@section('pagescripts')
    <script> $(function() { new FroalaEditor(".apply-froala",{height:200, toolbarButtons:['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'color', 'inlineClass', 'inlineStyle', 'paragraphStyle', 'lineHeight', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'embedly', 'insertTable', '|', 'emoticons', 'fontAwesome', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'spellChecker', 'help', 'html', '|', 'undo', 'redo']}) }); </script>
@endsection
@section('pagestyles')
    <style>
        a[href="https://www.froala.com/wysiwyg-editor?k=u"]{
            display:none!important;
        }
    </style>
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
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
                        {{Form::open(['url'=>'generateofficeorder'])}}
                            {{Form::hidden('Id',$id)}}

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="ReferenceNo">Ref No. <span class="required">*</span></label>
                                        <input type="text" autocomplete="off" id="ReferenceNo" required="required" name="ReferenceNo" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="Date"> Date <span class="required">*</span></label>
                                        <input type="date" autocomplete="off" id="Date" required="required" name="Date" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if($hasEffectiveDate)
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="EffectiveDate">Effective Date <span class="required">*</span></label>
                                            <input type="date" autocomplete="off" id="EffectiveDate" required="required" name="EffectiveDate" autocomplete="off" class="form-control"/>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="EvaluationMeetingDate">Date of Evaluation Meeting <span class="required">*</span></label>
                                        <input type="date" autocomplete="off" value="{{$evaluationMeetingDate}}" id="EvaluationMeetingDate" required="required" name="EvaluationMeetingDate" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            @if($hasWysiwyg)
                            <textarea name="Content" class="apply-froala"></textarea><br>
                            @endif
                            <button type="submit" class="btn btn-primary dont-disable">Generate</button>
                            <a href="{{url('generateofficeorder')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
