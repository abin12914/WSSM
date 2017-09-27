@extends('layouts.app')
@section('title', 'Product Registration')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Product
            <small>Registartion</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Product Registration</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        @if (Session::has('message'))
            <div class="alert {{ Session::get('alert-class', 'alert-info') }}" id="alert-message">
                <h4>
                  {!! Session::get('message') !!}
                </h4>
            </div>
        @endif
        <!-- Main row -->
        <div class="row  no-print">
            <div class="col-md-12">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title" style="float: left;">Product Registration</h3>
                            <p>&nbsp&nbsp&nbsp(Fields marked with <b style="color: red;">* </b>are mandatory.)</p>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form action="{{route('product-register-action')}}" method="post" class="form-horizontal">
                        <div class="box-body">
                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                            <div class="row">
                                <div class="col-md-11">
                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label"><b style="color: red;">* </b> Product Name : </label>
                                        <div class="col-sm-10 {{ !empty($errors->first('name')) ? 'has-error' : '' }}">
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Product name" value="{{ old('name') }}" tabindex="1">
                                            @if(!empty($errors->first('name')))
                                                <p style="color: red;" >{{$errors->first('name')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><b style="color: red;">* </b>Product Category : </label>
                                        <div class="col-sm-10 {{ !empty($errors->first('category_id')) ? 'has-error' : '' }}">
                                            <select class="form-control" name="category_id" id="category_id" tabindex="5">
                                                <option value="" {{ empty(old('category_id')) ? 'selected' : '' }}>Select product category</option>
                                                @if(!empty($productCategories))
                                                    @foreach($productCategories as $category)
                                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if(!empty($errors->first('category_id')))
                                                <p style="color: red;" >{{$errors->first('category_id')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="product_code" class="col-sm-2 control-label"><b style="color: red;">* </b> Product Code : </label>
                                        <div class="col-sm-10 {{ !empty($errors->first('product_code')) ? 'has-error' : '' }}">
                                            <input type="text" name="product_code" class="form-control" id="product_code" placeholder="Product code" value="{{ old('product_code') }}" tabindex="1">
                                            @if(!empty($errors->first('product_code')))
                                                <p style="color: red;" >{{$errors->first('product_code')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-2 control-label">Description : </label>
                                        <div class="col-sm-10 {{ !empty($errors->first('description')) ? 'has-error' : '' }}">
                                            @if(!empty(old('description')))
                                                <textarea class="form-control" name="description" id="description" rows="3" placeholder="Product description" style="resize: none;" tabindex="2">{{ old('description') }}</textarea>
                                            @else
                                                <textarea class="form-control" name="description" id="description" rows="3" placeholder="Product description" style="resize: none;" tabindex="2"></textarea>
                                            @endif
                                            @if(!empty($errors->first('description')))
                                                <p style="color: red;" >{{$errors->first('description')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><b style="color: red;">* </b>Measure Unit : </label>
                                        <div class="col-sm-10 {{ !empty($errors->first('measure_unit')) ? 'has-error' : '' }}">
                                            <select class="form-control" name="measure_unit" id="measure_unit" tabindex="5">
                                                <option value="" {{ empty(old('measure_unit')) ? 'selected' : '' }}>Select product measure unit</option>
                                                @if(!empty($measureUnits))
                                                    @foreach($measureUnits as $measureUnit)
                                                        <option value="{{ $measureUnit->id }}" {{ old('measure_unit') == $measureUnit->id ? 'selected' : '' }}>{{ $measureUnit->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if(!empty($errors->first('measure_unit')))
                                                <p style="color: red;" >{{$errors->first('measure_unit')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><b style="color: red;">* </b> SGST : </label>
                                        <div class="col-sm-10 {{ !empty($errors->first('sgst')) ? 'has-error' : '' }}">
                                            <input type="text" class="form-control decimal_number_only" name="sgst" id="sgst" placeholder="State GST rate" value="{{ old('sgst') }}" tabindex="3">
                                            @if(!empty($errors->first('sgst')))
                                                <p style="color: red;" >{{$errors->first('sgst')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><b style="color: red;">* </b> CGST : </label>
                                        <div class="col-sm-10 {{ !empty($errors->first('cgst')) ? 'has-error' : '' }}">
                                            <input type="text" class="form-control decimal_number_only" name="cgst" id="cgst" placeholder="Central GST rate" value="{{ old('cgst') }}" tabindex="4">
                                            @if(!empty($errors->first('cgst')))
                                                <p style="color: red;" >{{$errors->first('cgst')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"> </div><br>
                            <div class="row">
                                <div class="col-xs-3"></div>
                                <div class="col-xs-3">
                                    <button type="reset" class="btn btn-default btn-block btn-flat" tabindex="6">Clear</button>
                                </div>
                                <div class="col-xs-3">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat submit-button" tabindex="5">Submit</button>
                                </div>
                                <!-- /.col -->
                            </div><br>
                        </div>
                    </form>
                </div>
                <!-- /.box primary -->
            </div>
            </div>
        </div>
        <!-- /.row (main row) -->
    </section>
    <!-- /.content -->
</div>
@endsection