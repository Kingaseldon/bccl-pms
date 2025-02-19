@extends('master')
@section('page-title','Categories')
{{--@section('page-header','Your Profile')--}}
@section('page-header',"Manage Categories")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            {{--<br><br><br><br>--}}
            <div class="row"> {{--here--}}
                <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{url('categoryinput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <div class="table-responsive">
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                        {{--<tr>--}}
                                            {{--<th>Sl. #</th>--}}
                                            {{--<th>Name</th>--}}
                                            {{--<th>Email</th>--}}
                                            {{--<th>Extension</th>--}}
                                            {{--<th>Designation</th>--}}
                                            {{--<th>Department</th>--}}
                                            {{--<th>Role</th>--}}
                                            {{--<th>Status</th>--}}
                                        {{--</tr>--}}
                                        <tr>
                                            <th style="width: 10px;">Sl#</th>
                                            <th>Name</th>
                                            <th class="text-center" style="width:32%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <?php $slNo=app('request')->has('page')?(app('request')->input('page')-1)*$perPage+1:1; ?>
                                    @forelse($categories as $category)
                                        <tr>
                                            <td>{{$slNo++}}. </td>
                                            <td>{{$category->Name}}</td>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-primary editconfirm" href="{{url('categoryinput',[$category->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{url('categorydelete',[$category->Id])}}"><i class="fa fa-times"></i> Delete</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center"><strong>No data to display.</strong></td>
                                        </tr>
                                    @endforelse
                                </table>
                                {{$categories->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
