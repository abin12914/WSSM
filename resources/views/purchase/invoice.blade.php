@extends('layouts.app')
@section('title', 'Purchase Invoice')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Purchase
            <small>Invoice</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Purchase Invoice</li>
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
        <div class="row no-print">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <div class="row"><br>
                                <div class="col-md-11">
                                    <div class="form-group">
                                        <label for="date_credit" class="col-sm-2 control-label">Date & Time : </label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control decimal_number_only datepicker" name="date" id="date_credit" placeholder="Date" value="" tabindex="50">
                                        </div>
                                        <div class="col-sm-5">
                                            <div class="bootstrap-timepicker">
                                                <input type="text" class="form-control timepicker" name="time" id="time_credit" placeholder="Time" value="" tabindex="51">
                                            </div>
                                            @if(!empty($errors->first('time')))
                                                <p style="color: red;" >{{$errors->first('time')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="supplier_account_id" class="col-sm-2 control-label"><b style="color: red;">* </b> Supplier : </label>
                                        <div class="col-sm-10">
                                            <label class="control-label">{{ $purchasedetail->transaction->creditAccount->account_name }}</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-2 control-label">Description : </label>
                                        <div class="col-sm-10">
                                            <label class="control-label">{{ $purchasedetail->description }}</label>
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
        <div class="row">
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
                                    <th style="width: 26%">Total</th>
                                    <th class="no-print" style="width: 2%">#</th>
                                </tr>
                            </thead>
                            <tbody id="bill_body">
                                @foreach($purchases as $index => $purchase)
                                    <tr>
                                        <td>{{ $index+1 }}</td>
                                        <td>
                                            <label class="form-control">{{ $purchase->purchaseDetail->product->name }}</label>
                                        <td>
                                            <label class="form-control">{{ $purchase->purchaseDetail->product->name }}</label>
                                            <input name="quantity_'{{ $index+1 }}'" class="form-control" type="text" style="width: 100%; height: 35px;" value="{{ $detail->quantity }}">
                                        </td>
                                        <td>
                                            <label class="form-control">{{ $purchase->purchaseDetail->product->name }}</label>
                                            <input id="measure_unit_'{{ $index+1 }}'" class="form-control" type="text" readonly style="width: 100%; height: 35px;" value="{{ $detail->product->measureUnit->name }}">
                                        </td>
                                        <td>
                                            <label class="form-control">{{ $purchase->purchaseDetail->product->name }}</label>
                                            <input name="rate'{{ $index+1 }}'" class="form-control" type="text" style="width: 100%; height: 35px;" value="{{ $detail->rate }}">
                                        </td>
                                        <td>
                                            <label class="form-control">{{ $purchase->purchaseDetail->product->name }}</label>
                                            <input name="sub_total'{{ $index+1 }}'" class="form-control" type="text" style="width: 100%; height: 35px;" value="{{ $detail->total }}">
                                        </td>
                                        <td class="no-print">
                                            <button data-detail-id="{{ $detail->id }}" id="remove_button_{{ $index + 1 }}" type="button" class="form-control remove_button">
                                                <i style="color: red;" class="fa fa-close"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td class="no-print"></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b>Total Bill</b></td>
                                    <td><input name="bill_amount" id="bill_amount" type="text" readonly class="form-control no-print" value="{{ $totalBill }}" style="width: 100%; height: 35px;"></td>
                                    <td class="no-print"><i style="color: blue;" class="fa  fa-flag-o"></i></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b>Tax</b></td>
                                    <td><input name="tax_amount" id="tax_amount" type="text" class="form-control no-print" value="0" readonly style="width: 100%; height: 35px;"></td>
                                    <td class="no-print"><i style="color: blue;" class="fa  fa-flag-o"></i></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b>Discount</b></td>
                                    <td><input name="discount" id="discount" type="text" class="form-control no-print" value="0" style="width: 100%; height: 35px;"></td>
                                    <td class="no-print"><i style="color: blue;" class="fa  fa-flag-o"></i></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b>Total</b></td>
                                    <td><input name="deducted_total" id="deducted_total" type="text" class="form-control no-print" value="{{ $totalBill }}" style="width: 100%; height: 35px;"></td>
                                    <td class="no-print"><i style="color: blue;" class="fa  fa-flag-o"></i></td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="clearfix"> </div><br>
                        <div class="row no-print">
                            <div class="col-xs-3"></div>
                            <div class="col-xs-3">
                                <button type="reset" class="btn btn-default btn-block btn-flat">Clear</button>
                            </div>
                            {{-- <div class="col-sm-1"></div> --}}
                            <div class="col-xs-3">
                                <button type="button" class="btn btn-primary btn-block btn-flat submit-button">Print</button>
                            </div>
                            <!-- /.col -->
                        </div><br>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection