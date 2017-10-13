<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetailTemp;
use App\Models\Transaction;
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
        $saleDetailTemp = SaleDetailTemp::where('status', 1)->get();
        $totalBill      = SaleDetailTemp::where('status', 1)->sum('total');
        $sales          = Sale::with(['transaction.debitAccount'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('sale.register',[
                'accounts'          => $accounts,
                'products'          => $products,
                'saleDetailTemp'    => $saleDetailTemp,
                'saleDetailTemp'    => $saleDetailTemp,
                'totalBill'         => $totalBill
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

        $salesAccount = Account::where('account_name','Sales')->first();
        if($salesAccount) {
            $salesAccountId = $salesAccount->id;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/01</small>")->with("alert-class","alert-danger");
        }

        $saleRecord = Account::find($customerAccountId);
        if($saleRecord) {
            $customer = $saleRecord->account_name;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/02</small>")->with("alert-class","alert-danger");
        }

        $totalBill  = SaleDetailTemp::where('status', 1)->sum('total');
        if(empty($totalBill) || $totalBill == 0) {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Minimum one product should be added to the bill!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
        }
        if($billAmount != $totalBill) {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Calculation Error. Try again after reloading the page!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
        }
        if(($billAmount + $taxAmount - $discount) != $deductedTotal) {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Calculation Error. Try again after reloading the page!<small class='pull-right'> #06/04</small>")->with("alert-class","alert-danger");
        }

        //converting date and time to sql datetime format
        $dateTime = date('Y-m-d H:i:s', strtotime($date.' '.$time.':00'));

        $transaction = new Transaction;
        $transaction->debit_account_id  = $customerAccountId; //customer account id
        $transaction->credit_account_id = $salesAccountId; //sales account
        $transaction->amount            = !empty($deductedTotal) ? $deductedTotal : '0';
        $transaction->date_time         = $dateTime;
        $transaction->particulars       = $description."[Sale to/by ". $customer."]";
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
                $saleDetailTemp = SaleDetailTemp::where('status', 1)->get();

                foreach ($saleDetailTemp as $key => $detail) {
                    $saleDetailArray[$key] = [
                            'sale_id'       => $sale->id,
                            'product_id'    => $detail->product_id,
                            'quantity'      => $detail->quantity,
                            'rate'          => $detail->rate,
                            'total'         => $detail->total,
                            'status'        => 1,
                        ];
                }
                if($sale->products()->sync($saleDetailArray)) {
                    SaleDetailTemp::truncate();
                    return redirect()->back()->with("message","Successfully saved.")->with("alert-class","alert-success");
                } else {
                    $sale->delete();
                    $transaction->delete();

                    return redirect()->back()->withInput()->with("message","Failed to save the truck type and royalty details. Try again after reloading the page!<small class='pull-right'> #13/01</small>")->with("alert-class","alert-danger");
                }
                return redirect()->back()->with("message","Successfully saved.")->with("alert-class","alert-success");
            } else {
                //delete the transaction if associated sale saving failed.
                $transaction->delete();

                return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
            }
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/04</small>")->with("alert-class","alert-danger");
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

    public function addSaleDetail(Request $request) {
        $productId  = $request->get('product_id');
        $quantity   = $request->get('quantity');
        $rate       = $request->get('rate');
        $total      = $request->get('total');

        $saleDetailTemp = new SaleDetailTemp();
        $saleDetailTemp->product_id = $productId;
        $saleDetailTemp->quantity   = $quantity;
        $saleDetailTemp->rate       = $rate;
        $saleDetailTemp->total      = $total;
        $saleDetailTemp->status     = 1;
        if($saleDetailTemp->save()) {
            $count      = SaleDetailTemp::where('status', 1)->count();
            $totalBill  = SaleDetailTemp::where('status', 1)->sum('total');
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

            $html = '<tr id="product_row_'.$saleDetailTemp->id.'">'.
                        '<td>'.($count).'</td>'.
                        '<td id="td_product_id_'.($count).'">'.
                            '<label class="form-control">'.$productName.'</label>'.
                        '<td>'.
                            '<input id="quantity_'.($count).'" class="form-control" type="text" style="width: 100%; height: 35px;" value="'.$quantity.'">'.
                        '</td>'.
                        '<td>'.
                            '<input id="measure_unit_'.($count).'" class="form-control" type="text" readonly style="width: 100%; height: 35px;" value="'.$measureUnit.'">'.
                        '</td>'.
                        '<td>'.
                            '<input id="rate'.($count).'" class="form-control" type="text" style="width: 100%; height: 35px;" value="'.$rate.'">'.
                        '</td>'.
                        '<td>'.
                            '<input id="sub_total'.($count).'" class="form-control" type="text" style="width: 100%; height: 35px;" value="'.$total.'">'.
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

    public function viewInvoice($invoiceId) {
        $sale = Sale::where('id', $invoiceId)->where('status', 1)->first();

        if(empty($sale)) {
            return redirect()->back()->withInput()->with("message","Invoice not found. Try again after reloading the page!<small class='pull-right'> #13/01</small>")->with("alert-class","alert-danger");
        }
        return view('sale.invoice',[
                'sale'  => $sale,
            ]);
    }
}
