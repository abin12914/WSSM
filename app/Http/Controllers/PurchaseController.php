<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetailTemp;
use App\Models\Transaction;
use Auth;
use DateTime;
use App\Http\Requests\PurchaseRegistrationRequest;

class PurchaseController extends Controller
{
    /**
     * Return view for purchase registration
     */
    public function register()
    {
        $accounts       = Account::where('type',3)->get();
        $cashAccount    = Account::find(1);
        $accounts->push($cashAccount); //attaching cash account to the accounts
        $products       = Product::get();
        $purchasedetailTemp = PurchaseDetailTemp::where('status', 1)->get();
        $totalBill  = PurchaseDetailTemp::where('status', 1)->sum('total');
        $purchases      = Purchase::with(['transaction.creditAccount'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('purchase.register',[
                'accounts'              => $accounts,
                'products'              => $products,
                'purchasedetailTemp'    => $purchasedetailTemp,
                'purchases'             => $purchases,
                'totalBill'             => $totalBill
            ]);
    }

    /**
     * Handle new purchase registration
     */
    public function registerAction(PurchaseRegistrationRequest $request)
    {
        $supplierAccountId  = $request->get('supplier_account_id');
        $date               = $request->get('date');
        $time               = $request->get('time');
        $description        = $request->get('description');
        $billAmount         = $request->get('bill_amount');
        $taxAmount          = $request->get('tax_amount');
        $discount           = $request->get('discount');
        $deductedTotal      = $request->get('deducted_total');

        $purchaseAccount = Account::where('account_name','Purchases')->first();
        if($purchaseAccount) {
            $purchaseAccountId = $purchaseAccount->id;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the purchase details. Try again after reloading the page!<small class='pull-right'> #06/01</small>")->with("alert-class","alert-danger");
        }

        $supplierRecord = Account::find($supplierAccountId);
        if($supplierRecord) {
            $supplier = $supplierRecord->account_name;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the purchase details. Try again after reloading the page!<small class='pull-right'> #06/02</small>")->with("alert-class","alert-danger");
        }

        $totalBill  = PurchaseDetailTemp::where('status', 1)->sum('total');
        if(empty($totalBill) || $totalBill == 0) {
            return redirect()->back()->withInput()->with("message","Failed to save the purchase details. Minimum one product should be added to the bill!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
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
        $transaction->debit_account_id  = $purchaseAccountId; //purchase account id
        $transaction->credit_account_id = $supplierAccountId; //supplier
        $transaction->amount            = !empty($deductedTotal) ? $deductedTotal : '0';
        $transaction->date_time         = $dateTime;
        $transaction->particulars       = $description."[Purchase from/by ". $supplier."]";
        $transaction->status            = 1;
        $transaction->created_by        = Auth::user()->id;
        if($transaction->save()) {
            $purchase = new Purchase;
            $purchase->transaction_id   = $transaction->id;
            $purchase->bill_amount      = $billAmount;
            $purchase->tax_amount       = $taxAmount;
            $purchase->discount         = $discount;
            $purchase->total            = $deductedTotal;
            $purchase->status           = 1;
            
            if($purchase->save()) {
                $purchasedetailTemp = PurchaseDetailTemp::where('status', 1)->get();

                foreach ($purchasedetailTemp as $key => $detail) {
                    $purchaseDetailArray[$key] = [
                            'purchase_id'   => $purchase->id,
                            'product_id'    => $detail->product_id,
                            'quantity'      => $detail->quantity,
                            'rate'          => $detail->rate,
                            'total'         => $detail->total,
                            'status'        => 1,
                        ];
                }
                if($purchase->products()->sync($purchaseDetailArray)) {
                    PurchaseDetailTemp::truncate();
                    return redirect()->back()->with("message","Successfully saved.")->with("alert-class","alert-success");
                } else {
                    $purchase->delete();
                    $transaction->delete();

                    return redirect()->back()->withInput()->with("message","Failed to save the truck type and royalty details. Try again after reloading the page!<small class='pull-right'> #13/01</small>")->with("alert-class","alert-danger");
                }
                return redirect()->back()->with("message","Successfully saved.")->with("alert-class","alert-success");
            } else {
                //delete the transaction if associated purchase saving failed.
                $transaction->delete();

                return redirect()->back()->withInput()->with("message","Failed to save the purchase details. Try again after reloading the page!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
            }
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the purchase details. Try again after reloading the page!<small class='pull-right'> #06/04</small>")->with("alert-class","alert-danger");
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
        $productId      = !empty($request->get('product_id')) ? $request->get('product_id') : 0;

        $accounts       = Account::where('type', 'personal')->where('status', '1')->get();
        $cashAccount    = Account::find(1);
        if(!empty($cashAccount) && count($cashAccount) == 1) {
            $accounts->push($cashAccount); //attaching cash account to the accounts
        }
        $products       = Product::where('status', '1')->get();

        $query = Purchase::where('status', 1);

        if(!empty($accountId) && $accountId != 0) {
            $query = $query->whereHas('transaction', function ($qry) use($accountId) {
                $qry->where('credit_account_id', $accountId);
            });
        }

        if(!empty($productId) && $productId != 0) {
            $query = $query->where('product_id', $productId);
        }

        $totalQuery     = clone $query;
        $totalAmount    = $totalQuery->sum('total');

        $purchases = $query->with(['transaction.creditAccount'])->orderBy('id','desc')->paginate(15);
        
        return view('purchase.list',[
                'accounts'              => $accounts,
                'products'              => $products,
                'purchases'             => $purchases,
                'accountId'             => $accountId,
                'productId'             => $productId,
                'fromDate'              => $fromDate,
                'toDate'                => $toDate,
                'totalAmount'           => $totalAmount
            ]);
    }

    public function addPurchaseDetail(Request $request) {
        $productId  = $request->get('product_id');
        $quantity   = $request->get('quantity');
        $rate       = $request->get('rate');
        $total      = $request->get('total');

        $purchasedetailTemp = new PurchaseDetailTemp();
        $purchasedetailTemp->product_id = $productId;
        $purchasedetailTemp->quantity   = $quantity;
        $purchasedetailTemp->rate       = $rate;
        $purchasedetailTemp->total      = $total;
        $purchasedetailTemp->status     = 1;
        if($purchasedetailTemp->save()) {
            $count      = PurchaseDetailTemp::where('status', 1)->count();
            $totalBill  = PurchaseDetailTemp::where('status', 1)->sum('total');
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

            $html = '<tr id="product_row_'.$purchasedetailTemp->id.'">'.
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
                            '<button data-detail-id="'. $purchasedetailTemp->id .'" id="remove_button_'.($count).'" type="button" class="form-control remove_button">'.
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

    public function deletePurchaseDetail(Request $request) {
        $id = $request->get('id');
        $purchaseDetail = PurchaseDetailTemp::find($id);
        if(!empty($purchaseDetail)) {
            $amount = $purchaseDetail->total;
        } else {
            return([
                    'flag' => false
                ]);
        }

        if($purchaseDetail->delete()) {
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
}
