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
        <div class="row">
            <div class="col-md-12">
            <div class="col-md-3"></div>
            <div class="col-md-6"><h1 class="visible-print" style="text-align:center;">CHAKKAPPAN KERALA MARKET</h1></div><div class="clearfix"></div>
            <div class="col-md-4"></div>
            <div class="col-md-4"><H4 class="visible-print" style="text-align:center;">Ph : 08943091419, 09745307548</H4></div><div class="clearfix"></div>
            <div class="col-md-4"></div>
            <div class="col-md-4"><H4 class="visible-print" style="text-align:center;">PURCHASE INVOICE</H4></div><div class="clearfix"></div>
                <div class="box">
                    <div class="box-body">
                        <div class="row"><br>
                            <div class="col-md-1"></div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <label for="date_credit" class="control-label">Invoice No & Date : </label>
                                        <label class="form-control">{{ $purchase->id }} / {{ Carbon\Carbon::parse($purchase->transaction->date_time)->format('d-m-Y') }}</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="supplier_account_id" class="control-label">Supplier : </label>
                                        <label class="form-control">{{ $purchase->transaction->creditAccount->account_name }}</label>
                                    </div>
                                </div>
                                <div class="form-group {{ empty($purchase->description) ? 'no-print' : '' }}">
                                    <div class="col-sm-12">
                                        <label for="description" class="control-label">Description : </label>
                                        <label class="form-control">{{ $purchase->description }}</label>
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
                                        @foreach($purchase->products as $index => $product)
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
                                            <td>{{ $purchase->bill_amount }}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>Tax</b></td>
                                            <td>{{ $purchase->tax_amount }}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>Discount</b></td>
                                            <td>{{ $purchase->discount }}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>Total</b></td>
                                            <td>{{ $purchase->total }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="col-md-4"></div>
                                <div class="col-md-4"><h4 class="visible-print" style="text-align:center;"><U>VIJO STORES</U></h4></div><div class="clearfix"></div>
                                <div class="col-md-1"></div>
                                <div class="col-md-8"><h4 class="visible-print">South Indian Bank A/c No : 0396073000000607, IFSC : SIBL0000396</h4></div>
                                <div class="clearfix"></div>
                                <div class="col-md-1"></div>
                                <div class="col-md-8"><h4 class="visible-print">State Bank Of India A/c No : 00000067374490183, IFSC : SBTR0001200</h4></div>
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