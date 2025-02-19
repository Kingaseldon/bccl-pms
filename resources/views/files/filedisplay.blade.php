@extends('master')
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row"> {{--here--}}
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-10">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="{{Request::url()}}" method="GET">
                            <div class="row">
                                {{--<div class="col-sm-12">--}}
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2 filter-category-ajax" id="DepartmentId">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == app('request')->input('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <label for="CategoryId" class="control-label">Category</label>
                                        <select name="CategoryId" class="form-control category" id="CategoryId">
                                            <option value="">All</option>
                                            @foreach($categories as $category)
                                                <option data-departmentid="{{$category->DepartmentId}}" @if($category->Id == app('request')->input('CategoryId'))selected="selected"@endif value="{{$category->Id}}">{{$category->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <label for="FileName">File Name</label>
                                        <input type="text" id="FileName" value="{{app('request')->input('FileName')}}" name="FileName" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-5 col-md-5 col-sm-5 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{url('files')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
                                        </div>
                                    </div>
                                </div>
                                {{--</div>--}}
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed table-bordered font-small">
                                        <thead>
                                            <tr>
                                                <th width="20">Sl#</th>
                                                <th>Department/Unit</th>
                                                <th>Category</th>
                                                <th class="text-center">File</th>
                                            </tr>
                                        </thead>
                                        <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                        @forelse($files as $file)
                                            <?php $depts = $file->Depts; $deptsArray = explode(",",$depts); $visibility = false; ?>
                                            @if(in_array(99,$deptsArray))
                                                @if(Auth::user()->PositionId == CONST_POSITION_HOD || Auth::user()->PositionId == CONST_POSITION_MD)
                                                    <?php $visibility = true; ?>
                                                @endif
                                            @endif
                                            @if(in_array(Auth::user()->DepartmentId,$deptsArray))
                                                <?php $visibility = true; ?>
                                            @endif
                                            @if($visibility == true)
                                                <tr>
                                                    <td>{{$slNo++}}. </td>
                                                    <td>{{$file->Department}}</td>
                                                    <td>{{$file->Category}}</td>
                                                    <td class="text-center"><form action="{{url("filedisplay")}}" method="POST" target="_blank">{{Form::token()}}<input type="hidden" name="z" value="{{Crypt::encrypt($file->FilePath)}}"/><input type="hidden" name="w" value="{{Crypt::encrypt($file->Name)}}"/><button type="submit" class="dont-disable btn btn-xs btn-primary"><i class="fa fa-download"></i> {{$file->Name}}</button></form></td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center"><strong>No data to display.</strong></td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                                {{$files->appends(app('request')->except('page'))->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="file-modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="#" style="width:100%; height: 600px;" id="file-iframe"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
