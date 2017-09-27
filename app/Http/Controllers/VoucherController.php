<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use DateTime;
use App\Models\Account;
use App\Models\Voucher;
use App\Models\Transaction;
use App\Http\Requests\VoucherRegistrationRequest;

class VoucherController extends Controller
{
    /**
     * Return view for daily statement registration
     */
    public function register()
    {
        $today = Carbon::now('Asia/Kolkata');
        
        $cashVouchers   = Voucher::where('voucher_type','Cash')->with(['transaction.creditAccount'])->orderBy('created_at', 'desc')->take(5)->get();
        $accounts       = Account::where('type','personal')->get();

        return view('voucher.register',[
                'today' => $today,
                'accounts'          => $accounts,
                'cashVouchers'      => $cashVouchers,
            ]);
    }

    /**
     * Handle new cash voucher registration
     */
    public function registerAction(VoucherRegistrationRequest $request)
    {
        $date                   = $request->get('cash_voucher_date');
        $time                   = $request->get('cash_voucher_time');
        $accountId              = $request->get('cash_voucher_account_id');
        $voucherTransactionType = $request->get('cash_voucher_type');
        $voucherAmount          = $request->get('cash_voucher_amount');
        $description            = $request->get('cash_voucher_description');

        $cashAccount = Account::where('account_name','Cash')->first();
        if($cashAccount) {
            $cashAccountId = $cashAccount->id;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the voucher details. Try again after reloading the page!<small class='pull-right'> #06/01</small>")->with("alert-class","alert-danger");
        }

        $account = Account::where('id',$accountId)->first();
        if($account) {
            $name = $account->accountDetail->name;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the voucher details. Try again after reloading the page!<small class='pull-right'> #06/02</small>")->with("alert-class","alert-danger");
        }

        if($voucherTransactionType == 1) {
            $debitAccountId     = $cashAccountId;
            $creditAccountId    = $accountId;
            $particulars        = $description." :(Cash recieved from ".$name.")";
        } else {
            $debitAccountId     = $accountId;
            $creditAccountId    = $cashAccountId;
            $particulars        = $description." :(Cash paid to ".$name.")";
        }

        //converting date and time to sql datetime format
        $dateTime = date('Y-m-d H:i:s', strtotime($date.' '.$time.':00'));

        $transaction = new Transaction;
        $transaction->debit_account_id  = $debitAccountId;
        $transaction->credit_account_id = $creditAccountId;
        $transaction->amount            = !empty($voucherAmount) ? $voucherAmount : '0';
        $transaction->date_time         = $dateTime;
        $transaction->particulars       = $particulars;
        $transaction->status            = 1;
        $transaction->created_user_id   = Auth::user()->id;
        if($transaction->save()) {
            $voucher = new Voucher;
            $voucher->date_time        = $dateTime;
            $voucher->voucher_type     = 'Cash';
            $voucher->transaction_type = $voucherTransactionType;
            $voucher->amount           = $voucherAmount;
            $voucher->description      = $description;
            $voucher->transaction_id   = $transaction->id;
            $voucher->status           = 1;
            
            if($voucher->save()) {
                return redirect()->back()->with("message","Successfully saved.")->with("alert-class","alert-success");
            } else {
                //delete transaction if associated voucher record saving failed.
                $transaction->delete();

                return redirect()->back()->withInput()->with("message","Failed to save the voucher details. Try again after reloading the page!<small class='pull-right'> #06/03</small>")->with("alert-class","alert-danger");
            }
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the voucher details. Try again after reloading the page!<small class='pull-right'> #06/04</small>")->with("alert-class","alert-danger");
        }
    }

    /**
     * Return view for list voucher / cash
     */
    public function cashVoucherList(Request $request)
    {
        $totalAmount        = 0;
        $accountId          = !empty($request->get('cash_voucher_account_id')) ? $request->get('cash_voucher_account_id') : 0;
        $transactionType    = !empty($request->get('transaction_type')) ? $request->get('transaction_type') : 0;
        $fromDate           = !empty($request->get('cash_voucher_from_date')) ? $request->get('cash_voucher_from_date') : '';
        $toDate             = !empty($request->get('cash_voucher_to_date')) ? $request->get('cash_voucher_to_date') : '';

        $accounts   = Account::where('type', 'personal')->where('status', '1')->get();

        $query = Voucher::where('status', 1)->where('voucher_type', 'Cash');

        if(!empty($accountId) && $accountId != 0) {
            $query = $query->whereHas('transaction', function ($q) use($accountId) {
                $q->where('credit_account_id', $accountId)->orWhere('debit_account_id', $accountId);
            });
        }

        if(!empty($transactionType) && $transactionType != 0) {
            $query = $query->where('transaction_type', $transactionType);
        }

        if(!empty($fromDate)) {
            $searchFromDate = new DateTime($fromDate);
            $searchFromDate = $searchFromDate->format('Y-m-d H:i');
            $query = $query->where('date_time', '>=', $searchFromDate);
        }

        if(!empty($toDate)) {
            $searchToDate = new DateTime($toDate);
            $searchToDate = $searchToDate->format('Y-m-d H:i');
            $query = $query->where('date_time', '<=', $searchToDate);
        }

        $totalQuery     = clone $query;
        $totalAmount    = $totalQuery->sum('amount');

        $cashVouchers = $query->with(['transaction.debitAccount.accountDetail', 'transaction.creditAccount.accountDetail'])->orderBy('date_time','desc')->paginate(15);
        
        return view('voucher.list',[
                'accounts'        => $accounts,
                'cashVouchers'    => $cashVouchers,
                'accountId'       => $accountId,
                'transactionType' => $transactionType,
                'fromDate'        => $fromDate,
                'toDate'          => $toDate,
                'creditVouchers'  => [],
                'machineVouchers' => [],
                'totalAmount'     => $totalAmount
            ]);
    }
}
