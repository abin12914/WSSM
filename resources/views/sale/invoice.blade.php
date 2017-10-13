@extends('layouts.app')
@section('title', 'Sale Invoice')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Sale
            <small>Invoice</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Sale Invoice</li>
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
        <div class="row">
            <div class="col-md-12">
            <div class="col-md-3"></div>
            <div class="col-md-6"><h1 class="visible-print" style="text-align:center;">CHAKKAPPAN KERALA MARKET</h1></div><div class="clearfix"></div>
            <div class="col-md-4"></div>
            <div class="col-md-4"><H4 class="visible-print" style="text-align:center;">Ph : 08943091419, 09745307548</H4></div><div class="clearfix"></div>
            <div class="col-md-4"></div>
            <div class="col-md-4"><H4 class="visible-print" style="text-align:center;">SALE INVOICE</H4></div><div class="clearfix"></div>
                <div class="box visible-print">
                    <div class="box-body">
                        <div class="form-group visible-print">
                            <div class="col-sm-12">
                                <label for="date_credit" class="control-label">Invoice No & Date : </label>
                                <label><p>{{ $sale->id }} / {{ Carbon\Carbon::parse($sale->transaction->date_time)->format('d-m-Y') }}</p></label>
                                <div class="pull-right">
                                    <label for="supplier_account_id" class="control-label">Customer : </label>
                                    <label><p>{{ $sale->transaction->debitAccount->account_name }}</p></label>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="form-group {{ !empty($sale->transaction->particulars) ? 'visible-print' : 'no-print' }}">
                            <div class="col-sm-12">
                                <label for="description" class="control-label">Description : </label>
                                <label>{{ $sale->transaction->particulars }}</label>
                            </div>
                        </div> --}}
                    </div>
                </div>
                <div class="box">
                    <div class="box-body">
                        <div class="row"><br>
                            <div class="col-md-1"></div>
                            <div class="col-md-10">
                                <div class="form-group no-print">
                                    <div class="col-sm-6">
                                        <label for="date_credit" class="control-label">Invoice No & Date : </label>
                                        <label class="form-control">{{ $sale->id }} / {{ Carbon\Carbon::parse($sale->transaction->date_time)->format('d-m-Y') }}</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="supplier_account_id" class="control-label">Customer : </label>
                                        <label class="form-control">{{ $sale->transaction->debitAccount->account_name }}</label>
                                    </div>
                                </div>
                                <div class="form-group {{ empty($sale->description) ? 'no-print' : '' }}">
                                    <div class="col-sm-12">
                                        <label for="description" class="control-label">Description : </label>
                                        <label class="form-control">{{ $sale->transaction->particulars }}</label>
                                    </div>
                                </div>
                                <div class="clearfix"></div><br>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <td style="width: 2%">#</td>
                                            <td style="width: 30%">Product</td>
                                            <td style="width: 15%">Quantity</td>
                                            <td style="width: 10%">Unit</td>
                                            <td style="width: 15%">Unit Price</td>
                                            <td style="width: 28%">Total</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->products as $index => $product)
                                            <tr>
                                                <td>{{ $index+1 }}</td>
                                                <td>
                                                    {{ $product->name }}
                                                </td>
                                                <td>
                                                    {{ $product->pivot->quantity }}
                                                </td>
                                                <td>
                                                    {{ $product->measureUnit->name }}
                                                </td>
                                                <td>
                                                    {{ $product->pivot->rate }}
                                                </td>
                                                <td>
                                                    {{ $product->pivot->total }}
                                                </td>
                                            </tr>
                                        @endforeach
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
                                            <td>{{ $sale->bill_amount }}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>Tax</b></td>
                                            <td>{{ $sale->tax_amount }}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>Discount</b></td>
                                            <td>{{ $sale->discount }}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>Total</b></td>
                                            <td>{{ $sale->total }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="col-md-4"></div>
                                <div class="col-md-4"><h4 class="visible-print" style="text-align:center;"><U>VIJO STORES</U></h4></div><div class="clearfix"></div>
                                <div class="col-md-1"></div>
                                <div class="col-md-8"><p  class="visible-print">South Indian Bank A/c No : 0396073000000607, IFSC : SIBL0000396</p></div>
                                <div class="clearfix"></div>
                                <div class="col-md-1"></div>
                                <div class="col-md-8"><p  class="visible-print">State Bank Of India A/c No : 00000067374490183, IFSC : SBTR0001200</p></div>
                                <div class="clearfix"> </div><br>
                                <div class="row no-print">
                                    <div class="col-xs-4"></div>
                                    <div class="col-xs-4">
                                        <button type="button" id="print_invoice" class="btn btn-primary btn-block btn-flat">Print</button>
                                    </div>
                                    <!-- /.col -->
                                </div><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection