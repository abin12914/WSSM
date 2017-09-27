<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
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
        $accounts       = Account::where('type','personal')->get();
        $cashAccount    = Account::find(1);
        $accounts->push($cashAccount); //attaching cash account to the accounts
        $products       = Product::get();
        $purchases      = Purchase::with(['transaction.creditAccount'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('purchase.register',[
                'accounts'      => $accounts,
                'products'      => $products,
                'purchases'     => $purchases,
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

        $productId          = $request->get('product_id');
        $quantity           = $request->get('quantity');
        $rate               = $request->get('rate');
        $subTotal           = $request->get('sub_total');

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

        /*if(($quantity * $rate) != $billAmount) {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Calculation Error. Try again after reloading the page!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
        }*/
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
                foreach ($productId as $key => $id) {
                    $purchaseDetailArray[$id] = [
                            'quantity'      => $quantity[$key],
                            'rate'          => $rate[$key],
                            'total'     => $subTotal[$key],
                            'status'        => 1,
                        ];
                }
                if($purchase->products()->sync($purchaseDetailArray)) {
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

        /*if(!empty($fromDate)) {
            $searchFromDate = new DateTime($fromDate);
            $searchFromDate = $searchFromDate->format('Y-m-d');
            $query = $query->where('date_time', '>=', $searchFromDate);
        }

        if(!empty($toDate)) {
            $searchToDate = new DateTime($toDate." 23:59");
            $searchToDate = $searchToDate->format('Y-m-d H:i');
            $query = $query->where('date_time', '<=', $searchToDate);
        }*/

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
}
