@extends('master')
@section('page-title','Error List')
@section('page-header',"Error List")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <form action="{{Request::url()}}" method="GET">
                            <div class="row">
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label for="URL" class="control-label">URL</label>
                                        <select name="URL" id="URL" class="form-control select2">
                                            <option value="">All</option>
                                            @foreach($urls as $url)
                                                <option value="{{$url->URL}}" @if($url->URL == app('request')->input('URL'))selected="selected"@endif >{{$url->URL}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="FromDate" class="control-label">From Date</label>
                                        <input type="date" name="FromDate" value="{{app('request')->input('FromDate')}}" id="FromDate" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="ToDate" class="control-label">To Date</label>
                                        <input type="date" name="ToDate" value="{{app('request')->input('ToDate')}}" id="ToDate" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-3 col-md-4 col-sm-4 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('bugindex')}}" style="" class="btn btn-warning"><i class="fa fa-times"></i> Clear</a>
                                        </div>
                                    </div>
                                    <br>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px;">Sl#</th>
                                            <th>Code</th>
                                            <th style="max-width:45%;">Message</th>
                                            <th>URL</th>
                                            <th>Line Number</th>
                                            <th>Date and Time of Error</th>
                                            <th class="text-center" style="width:12%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                    @forelse($errorLogs as $log)
                                        <tr>
                                            <td>{{$slNo++}}. </td>
                                            <td>{!!$log->Code!!}</td>
                                            <td>{!!$log->Message!!}</td>
                                            <td>{!!$log->URL!!}</td>
                                            <td>{{$log->LineNo}}</td>
                                            <td>{!!$log->Date!!}</td>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-primary fetch-error-detail" data-id="{{$log->Id}}" href="#"><i class="fa fa-eye"></i> Details</a>&nbsp;&nbsp;
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center"><strong>No data to display.</strong></td>
                                        </tr>
                                    @endforelse
                                </table>
                                {{$errorLogs->appends(app('request')->except('page'))->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal" id="error-detail">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Error Details</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body"></div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
