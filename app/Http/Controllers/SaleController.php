<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Product;
use App\Models\Sale;
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
        $accounts       = Account::where('type','personal')->get();
        $cashAccount    = Account::find(1);
        $accounts->push($cashAccount); //attaching cash account to the accounts
        $products       = Product::get();
        $sales          = Sale::with(['transaction.debitAccount'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('sale.register',[
                'accounts'      => $accounts,
                'products'      => $products,
                'sales_records' => $sales,
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

        $productId          = $request->get('product_id');
        $quantity           = $request->get('quantity');
        $rate               = $request->get('rate');
        $subTotal           = $request->get('sub_total');

        $purchaseAccount = Account::where('account_name','Sales')->first();
        if($purchaseAccount) {
            $purchaseAccountId = $purchaseAccount->id;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/01</small>")->with("alert-class","alert-danger");
        }

        $customerRecord = Account::find($customerAccountId);
        if($customerRecord) {
            $customer = $customerRecord->account_name;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the sale details. Try again after reloading the page!<small class='pull-right'> #06/02</small>")->with("alert-class","alert-danger");
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
        $transaction->debit_account_id  = $purchaseAccountId; //sale account id
        $transaction->credit_account_id = $customerAccountId; //customer
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
                foreach ($productId as $key => $id) {
                    $saleDetailArray[$id] = [
                            'quantity'      => $quantity[$key],
                            'rate'          => $rate[$key],
                            'total'     => $subTotal[$key],
                            'status'        => 1,
                        ];
                }
                if($sale->products()->sync($saleDetailArray)) {
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
        $totalAmount        = 0;
        $totalLoad          = 0;
        $totalSingleLoad    = 0;
        $totalMultipleLoad  = 0;
        $totalQuantity      = 0;
        $accountId      = !empty($request->get('account_id')) ? $request->get('account_id') : 0;
        $fromDate       = !empty($request->get('from_date')) ? $request->get('from_date') : '';
        $toDate         = !empty($request->get('to_date')) ? $request->get('to_date') : '';
        $vehicleId      = !empty($request->get('vehicle_id')) ? $request->get('vehicle_id') : 0;
        $productId      = !empty($request->get('product_id')) ? $request->get('product_id') : 0;
        $vehicleTypeId  = !empty($request->get('vehicle_type_id')) ? $request->get('vehicle_type_id') : 0;

        $accounts       = Account::where('type', 'personal')->orWhere('id', 1)->where('status', '1')->get();
        $products       = Product::where('status', '1')->get();

        $query = Sale::where('status', 1);

        if(!empty($accountId) && $accountId != 0) {
            $query = $query->whereHas('transaction', function ($qry) use($accountId) {
                $qry->where('debit_account_id', $accountId);
            });
        }

        if(!empty($vehicleId) && $vehicleId != 0) {
            $query = $query->where('vehicle_id', $vehicleId);
        }

        if(!empty($productId) && $productId != 0) {
            $query = $query->where('product_id', $productId);
        }

        if(!empty($vehicleTypeId) && $vehicleTypeId != 0) {
            $query = $query->whereHas('vehicle', function ($qry) use($vehicleTypeId) {
                $qry->where('vehicle_type_id', $vehicleTypeId);
            });
        }

        if(!empty($fromDate)) {
            $searchFromDate = new DateTime($fromDate);
            $searchFromDate = $searchFromDate->format('Y-m-d');
            $query = $query->where('date_time', '>=', $searchFromDate);
        }

        if(!empty($toDate)) {
            $searchToDate = new DateTime($toDate." 23:59");
            $searchToDate = $searchToDate->format('Y-m-d H:i');
            $query = $query->where('date_time', '<=', $searchToDate);
        }

        $totalQuery     = clone $query;
        $totalAmount    = $totalQuery->sum('total');

        /*$totalMultipleLoadQuery = clone $query;
        $totalMultipleLoad      = $totalMultipleLoadQuery->where('measure_type', 3)->sum('quantity');*/

        $totalLoad = $totalMultipleLoad +$totalSingleLoad;

        $sales = $query->with(['transaction.debitAccount'])->orderBy('id','desc')->paginate(15);
        
        return view('sale.list',[
                'accounts'              => $accounts,
                'products'              => $products,
                'sales'                 => $sales,
                'accountId'             => $accountId,
                'vehicleId'             => $vehicleId,
                'productId'             => $productId,
                'vehicleTypeId'         => $vehicleTypeId,
                'fromDate'              => $fromDate,
                'toDate'                => $toDate,
                'totalAmount'           => $totalAmount,
                'totalLoad'             => $totalLoad,
                'totalQuantity'         => $totalQuantity
            ]);
    }
}
