@extends('layouts.app')
@section('title', 'Purchase Registration')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Purchase
            <small>Registartion</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Purchase Registration</li>
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
        @if (count($errors) > 0)
            <div class="alert alert-danger" id="alert-message">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- Main row -->
        <!-- form start -->
        <form action="{{route('purchase-register-action')}}" id="credit_sale_form" method="post" class="form-horizontal">
            <div class="row no-print">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-body">
                            <div class="col-md-2"></div>
                            <div class="col-md-8">
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                <div class="row"><br>
                                    <div class="col-md-11">
                                        <div class="form-group">
                                            <label for="date_credit" class="col-sm-2 control-label"><b style="color: red;">* </b> Date & Time : </label>
                                            <div class="col-sm-5 {{ !empty($errors->first('date')) ? 'has-error' : '' }}">
                                                <input type="text" class="form-control decimal_number_only datepicker" name="date" id="date_credit" placeholder="Date" value="{{ old('date') }}" tabindex="22">
                                                @if(!empty($errors->first('date')))
                                                    <p style="color: red;" >{{$errors->first('date')}}</p>
                                                @endif
                                            </div>
                                            <div class="col-sm-5 {{ !empty($errors->first('time')) ? 'has-error' : '' }}">
                                                <div class="bootstrap-timepicker">
                                                    <input type="text" class="form-control timepicker" name="time" id="time_credit" placeholder="Time" value="{{ old('time') }}" tabindex="23">
                                                </div>
                                                @if(!empty($errors->first('time')))
                                                    <p style="color: red;" >{{$errors->first('time')}}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="supplier_account_id" class="col-sm-2 control-label"><b style="color: red;">* </b> Supplier : </label>
                                            <div class="col-sm-10 {{ !empty($errors->first('supplier_account_id')) ? 'has-error' : '' }}">
                                                <select name="supplier_account_id" class="form-control" id="supplier_account_id" tabindex="2" style="width: 100%">
                                                    <option value="" {{ empty(old('supplier_account_id')) ? 'selected' : '' }}>Select supplier</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" {{ (old('supplier_account_id') == $account->id) ? 'selected' : '' }}>{{ $account->account_name }}</option>
                                                    @endforeach
                                                </select>
                                                @if(!empty($errors->first('supplier_account_id')))
                                                    <p style="color: red;" >{{$errors->first('supplier_account_id')}}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="description" class="col-sm-2 control-label">Description : </label>
                                            <div class="col-sm-10 {{ !empty($errors->first('description')) ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="description" id="date_credit" placeholder="Bill Number" value="{{ old('description') }}">
                                                @if(!empty($errors->first('description')))
                                                    <p style="color: red;" >{{$errors->first('description')}}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row no-print">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 2%">#</th>
                                        <th style="width: 30%">Product</th>
                                        <th style="width: 15%">Quantity</th>
                                        <th style="width: 10%">Unit</th>
                                        <th style="width: 15%">Unit Price</th>
                                        <th style="width: 28%">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="product_row">
                                        <td>
                                            1
                                        </td>
                                        <td>
                                            <select name="product_id[1]" class="form-control product" style="width: 100%">
                                                <option value="">Select product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input name="quantity[1]" type="text" style="width: 100%; height: 35px;">
                                        </td>
                                        <td>
                                            <input id="measure_unit" type="text" disabled style="width: 100%; height: 35px;">
                                        </td>
                                        <td>
                                            <input name="rate[1]" type="text" style="width: 100%; height: 35px;">
                                        </td>
                                        <td>
                                            <input name="sub_total[1]" type="text" style="width: 100%; height: 35px;">
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td><td></td><td></td><td></td><td></td><td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><b>Total Bill</b></td>
                                        <td><input name="bill_amount" type="text" style="width: 100%; height: 35px;"></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><b>Tax</b></td>
                                        <td><input name="tax_amount" type="text" style="width: 100%; height: 35px;"></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><b>Discount</b></td>
                                        <td><input name="discount" type="text" style="width: 100%; height: 35px;"></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><b>Total</b></td>
                                        <td><input name="deducted_total" type="text" style="width: 100%; height: 35px;"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="clearfix"> </div><br>
                            <div class="row">
                                <div class="col-xs-3"></div>
                                <div class="col-xs-3">
                                    <button type="reset" class="btn btn-default btn-block btn-flat">Clear</button>
                                </div>
                                {{-- <div class="col-sm-1"></div> --}}
                                <div class="col-xs-3">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat submit-button">Submit</button>
                                </div>
                                <!-- /.col -->
                            </div><br>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- /.form end -->
        <div class="modal" id="product_selection_modal">
            <div class="modal-dialog" style="width: 80%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Select products</h4>
                    </div>
                    <div class="modal-body">
                        {{-- <iframe src="{{ route('product-selection-list') }}" width="100%" height="700px;"></iframe> --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                        <button type="button" id="btn_cash_sale_modal_submit" class="btn btn-primary">Add Selected Prooducts</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
    </section>
    <!-- /.content -->
</div>
@endsection
@section('scripts')
    <script src="/js/registration/saleRegistration.js?rndstr={{ rand(1000,9999) }}"></script>
@endsection