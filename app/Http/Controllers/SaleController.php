<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetailTemp;
use App\Models\Transaction;
use App\Models\Voucher;
use Auth;
use DateTime;
use Carbon\Carbon;
use App\Http\Requests\SaleRegistrationRequest;

class SaleController extends Controller
{
    /**
     * Return view for sale registration
     */
    public function register()
    {
        $accounts       = Account::where('type', 3)->get();
        $cashAccount    = Account::find(1);
        $accounts->push($cashAccount); //attaching cash account to the accounts
        $products       = Product::get();        

        return view('sale.register',[
                'accounts'          => $accounts,
                'products'          => $products,
            ]);
    }

    /**
     * Handle new credit sale registration
     */
    public function registerAction(SaleRegistrationRequest $request)
    {
        $customerAccountId  = $request->get('customer_account_id');
        $date               = $request->get('date');
        $time               = $request->get('time');
        $description        = $request->get('description');
        $billAmount         = $request->get('bill_amount');
        $taxAmount          = $request->get('tax_amount');
        $discount           = $request->get('discount');
        $deductedTotal      = $request->get('deducted_total');
        $oldBalance         = $request->get('old_balance');
        $totalAmount        = $request->get('total_amount');
        $payment            = $request->get('payment');
        $balance            = $request->get('balance');

        $salesAccount = Account::where('account_name','Sales')->first();
        if($salesAccount) {
            $salesAccountId = $salesAccount->id;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/01</small>")->with("alert-class","alert-danger");
        }

        $customerRecord = Account::find($customerAccountId);
        if($customerRecord && !empty($customerRecord->id)) {
            $customer = $customerRecord->account_name;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/02</small>")->with("alert-class","alert-danger");
        }

        $totalBill  = SaleDetailTemp::where('status', 1)->where('account_id', $customerAccountId)->sum('total');
        if(empty($totalBill) || $totalBill == 0) {
            $customError = ['product' => 'Minimum one product should be added to the bill.'];
            return redirect()->back()->withInput()->withErrors($customError);
            //flash message
            //->with("message","Failed to save the sale details. Minimum one product should be added to the bill!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger")
        }
        if($billAmount != $totalBill) {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Calculation Error. Try again after reloading the page!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
        }
        if(($billAmount + $taxAmount - $discount) != $deductedTotal || ($deductedTotal + $oldBalance - $payment != $balance)) {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Calculation Error. Try again after reloading the page!<small class='pull-right'> #06/04</small>")->with("alert-class","alert-danger");
        }

        //converting date and time to sql datetime format
        $dateTime = date('Y-m-d H:i:s', strtotime($date.' '.$time.':00'));
//dd($customerAccountId);
        $transaction = new Transaction;
        $transaction->debit_account_id  = $customerAccountId; //customer account id
        $transaction->credit_account_id = $salesAccountId; //sales account
        $transaction->amount            = !empty($deductedTotal) ? $deductedTotal : '0';
        $transaction->date_time         = $dateTime;
        $transaction->particulars       = ($description. "[Sale to/by " .$customer. "]");
        $transaction->status            = 1;
        $transaction->created_by        = Auth::user()->id;
        if($transaction->save()) {
            $sale = new Sale;
            $sale->transaction_id   = $transaction->id;
            $sale->bill_amount      = $billAmount;
            $sale->tax_amount       = $taxAmount;
            $sale->discount         = $discount;
            $sale->total            = $deductedTotal;
            $sale->status           = 1;
            
            if($sale->save()) {
                $saleDetailTemp = SaleDetailTemp::where('status', 1)->where('account_id', $customerAccountId)->get();

                foreach ($saleDetailTemp as $key => $detail) {
                    $saleDetailArray[$key] = [
                            'sale_id'       => $sale->id,
                            'account_id'    => $customerAccountId,
                            'product_id'    => $detail->product_id,
                            'quantity'      => $detail->quantity,
                            'rate'          => $detail->rate,
                            'total'         => $detail->total,
                            'status'        => 1,
                        ];
                }
                if($sale->products()->sync($saleDetailArray)) {
                    if($payment >= 1) {
                        $flag = $this->savePaymentVoucher($customerAccountId, $payment, $dateTime, $customer, $sale->id);
                        if($flag != 1) {
                            $sale->products()->detach();
                            $sale->delete();
                            $transaction->delete();
                            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #13/01</small>")->with("alert-class","alert-danger");
                        }
                    }

                    $deletingItems = SaleDetailTemp::where('account_id', $customerAccountId);
                    $deletingItems->delete();
                    return redirect()->back()->with("message","Successfully saved.")->with("alert-class","alert-success");
                } else {
                    $sale->delete();
                    $transaction->delete();

                    return redirect()->back()->withInput()->with("message","Failed to save sale details. Try again after reloading the page!<small class='pull-right'> #13/01</small>")->with("alert-class","alert-danger");
                }
            } else {
                //delete the transaction if associated sale saving failed.
                $transaction->delete();

                return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
            }
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/04</small>")->with("alert-class","alert-danger");
        }
    }

    public function savePaymentVoucher($customerAccountId, $payment, $dateTime, $customer, $saleId)
    {
        $cashAccount    = 0;

        if(empty($customerAccountId) || empty($payment) || empty($dateTime) || empty($customer) || empty($saleId)) {
            return 2;
        }
        $cashAccount    = Account::where('account_name','Cash')->first();
        if($cashAccount && !empty($cashAccount->id)) {
            $cashAccountId = $cashAccount->id;
        } else {
            return 3;
        }
        $description = ("Payment recieved from ". $customer ." with ". $dateTime ." sale [#". $saleId ."]");

        $paymentTransaction = new Transaction;
        $paymentTransaction->debit_account_id   = $cashAccountId;
        $paymentTransaction->credit_account_id  = $customerAccountId;
        $paymentTransaction->amount             = $payment;
        $paymentTransaction->date_time          = $dateTime;
        $paymentTransaction->particulars        = $description;
        $paymentTransaction->status             = 1;
        $paymentTransaction->created_by         = Auth::user()->id;
        if($paymentTransaction->save()) {
            $voucher = new Voucher;
            $voucher->voucher_type     = 1;
            $voucher->transaction_type = 1; //cash debit
            $voucher->amount           = $payment;
            $voucher->description      = $description;
            $voucher->transaction_id   = $paymentTransaction->id;
            $voucher->status           = 1;
            
            if($voucher->save()) {
                return 1;
            }
        }
    }

    /**
     * Return view for account listing
     */
    public function list(Request $request)
    {
        $accountId      = !empty($request->get('account_id')) ? $request->get('account_id') : 0;
        $fromDate       = !empty($request->get('from_date')) ? $request->get('from_date') : '';
        $toDate         = !empty($request->get('to_date')) ? $request->get('to_date') : '';

        $accounts       = Account::where('type', 3)->where('status', '1')->get();
        $cashAccount    = Account::find(1);
        if(!empty($cashAccount) && count($cashAccount) == 1) {
            $accounts->push($cashAccount); //attaching cash account to the accounts
        }

        $query = Sale::where('status', 1);

        if(!empty($accountId) && $accountId != 0) {
            $query = $query->whereHas('transaction', function ($qry) use($accountId) {
                $qry->where('credit_account_id', $accountId);
            });
        }

        $totalQuery     = clone $query;
        $totalAmount    = $totalQuery->sum('total');

        $sales = $query->with(['transaction.creditAccount'])->orderBy('id','desc')->paginate(15);
        
        return view('sale.list',[
                'accounts'      => $accounts,
                'sales'         => $sales,
                'accountId'     => $accountId,
                'fromDate'      => $fromDate,
                'toDate'        => $toDate,
                'totalAmount'   => $totalAmount
            ]);
    }

    public function addSaleDetail(Request $request)
    {
        $accountId  = $request->get('account_id');
        $productId  = $request->get('product_id');
        $quantity   = $request->get('quantity');
        $rate       = $request->get('rate');
        $total      = $request->get('total');

        $saleDetailTemp = new SaleDetailTemp();
        $saleDetailTemp->account_id = $accountId;
        $saleDetailTemp->product_id = $productId;
        $saleDetailTemp->quantity   = $quantity;
        $saleDetailTemp->rate       = $rate;
        $saleDetailTemp->total      = $total;
        $saleDetailTemp->status     = 1;
        if($saleDetailTemp->save()) {
            $count      = SaleDetailTemp::where('status', 1)->where('account_id', $accountId)->count();
            $totalBill  = SaleDetailTemp::where('status', 1)->where('account_id', $accountId)->sum('total');
            if(empty($count)) {
                $count = 0;
            }
            if(empty($totalBill)) {
                $totalBill = 0;
            }
            $product    = Product::find($productId);
            if(!empty($product) && !empty($product->id)) {
                $productName    = $product->name;
                $measureUnit    = $product->measureUnit->name;
            } else {
                return([
                'flag'  => false,
                ]);
            }

            $html = '<tr id="product_row_'.$saleDetailTemp->id.'" data-tempdetail-id="'.$saleDetailTemp->id.'">'.
                        '<td class="serial_number">'.($count).'</td>'.
                        '<td id="td_product_id_'.($count).'">'.
                            '<label class="form-control">'.$productName.'</label>'.
                        '<td>'.
                            '<input id="quantity_'.($count).'" class="form-control quantity" type="text" style="width: 100%; height: 35px;" value="'.$quantity.'" data-default-quantity="'.$quantity.'">'.
                        '</td>'.
                        '<td>'.
                            '<input id="measure_unit_'.($count).'" class="form-control" type="text" readonly style="width: 100%; height: 35px;" value="'.$measureUnit.'">'.
                        '</td>'.
                        '<td>'.
                            '<input id="rate_'.($count).'" class="form-control rate" type="text" style="width: 100%; height: 35px;" value="'.$rate.'" data-default-rate="'.$rate.'">'.
                        '</td>'.
                        '<td>'.
                            '<input id="sub_total_'.($count).'" class="form-control sub_total" type="text" style="width: 100%; height: 35px;" value="'.$total.'" readonly>'.
                        '</td>'.
                        '<td class="no-print">'.
                            '<button data-detail-id="'. $saleDetailTemp->id .'" id="remove_button_'.($count).'" type="button" class="form-control remove_button">'.
                                '<i style="color: red;" class="fa fa-close"></i>'.
                            '</button>'.
                        '</td>'.
                    '</tr>';
            return([
                'flag'      => true,
                'data'      => $html,
                'totalBill' => $totalBill,
                ]);
        } else {
            return([
                'flag'  => false,
                ]);
        }
    }

    public function deleteSaleDetail(Request $request) {
        $id = $request->get('id');
        $saleDetail = SaleDetailTemp::find($id);
        if(!empty($saleDetail)) {
            $amount = $saleDetail->total;
        } else {
            return([
                    'flag' => false
                ]);
        }

        if($saleDetail->delete()) {
            return([
                    'flag'      => true,
                    'amount'    => $amount
                ]);
        } else {
            return([
                    'flag' => false
                ]);
        }
    }

    public function editSaleDetail(Request $request) {
        $id         = $request->get('id');
        $rate       = !empty($request->get('rate')) ? $request->get('rate') : 0;
        $quantity   = !empty($request->get('quantity')) ? $request->get('quantity') : 0;

        $saleDetail = SaleDetailTemp::find($id);
        if(!empty($saleDetail) && !empty($saleDetail->account_id)) {
            $accountId  = $saleDetail->account_id;

            if(!empty($rate) && $rate > 0) {
                $quantity   = $saleDetail->quantity;
                $total      = $quantity * $rate;

                $saleDetail->rate   = $rate;
                $saleDetail->total  = $total;
            }
            if(!empty($quantity) && $quantity >0) {
                $rate   = $saleDetail->rate;
                $total  = $quantity * $rate;

                $saleDetail->quantity   = $quantity;
                $saleDetail->total      = $total;
            }
        } else {
            return([
                    'flag' => false
                ]);
        }

        if($saleDetail->save()) {
            $totalBill  = SaleDetailTemp::where('status', 1)->where('account_id', $accountId)->sum('total');
            return([
                    'flag'              => true,
                    'defaultRate'       => $saleDetail->rate,
                    'defaultQuantity'   => $saleDetail->quantity,
                    'totalBill'         => $totalBill,
                ]);
        } else {
            return([
                    'flag' => false
                ]);
        }
    }

    public function viewInvoice($invoiceId) {
        $sale = Sale::where('id', $invoiceId)->where('status', 1)->first();

        if(empty($sale)) {
            return redirect()->back()->withInput()->with("message","Invoice not found. Try again after reloading the page!<small class='pull-right'> #13/01</small>")->with("alert-class","alert-danger");
        }
        return view('sale.invoice',[
                'sale'  => $sale,
            ]);
    }

    public function getSaleDetailByAccountId($accountId)
    {
        $saleDetailTemp = SaleDetailTemp::where('status', 1)->where('account_id', $accountId)->with(['product.measureUnit'])->get();/*->whereHas('account', function ($qry) {$qry->where('type', 3);})*/
        $totalBill      = SaleDetailTemp::where('status', 1)->where('account_id', $accountId)->sum('total');/*->whereHas('account', function ($qry) {$qry->where('type', 3);})*/

        /*if(empty($saleDetailTemp) || count($saleDetailTemp) <= 0) {
            $saleDetailTemp = SaleDetailTemp::where('status', 2)->where('account_id', $accountId)->with(['product.measureUnit'])->get();
        }*/

        $totalDebit     = Transaction::where('debit_account_id', $accountId)->whereHas('debitAccount', function ($qry) {
                    $qry->where('type', 3);
                })->sum('amount');
        $totalCredit    = Transaction::where('credit_account_id', $accountId)->whereHas('creditAccount', function ($qry) {
                    $qry->where('type', 3);
                })->sum('amount');

        return([
            'flag'              => true,
            'totalDebit'        => $totalDebit,
            'totalCredit'       => $totalCredit,
            'saleDetailTemp'    => $saleDetailTemp->toJson(),
            'totalBill'         => $totalBill
        ]);
    }
}
