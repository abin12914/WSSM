<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AccountRegistrationRequest;
use App\Http\Requests\AccountUpdationRequest;
use App\Models\Account;
use App\Models\AccountDetail;
use App\Models\Transaction;
use DateTime;
use Auth;

class AccountController extends Controller
{
    /**
     * Return view for account registration
     */
    public function register()
    {
        return view('account.register');
    }

     /**
     * Handle new account registration
     */
    public function registerAction(AccountRegistrationRequest $request)
    {
        $saveFlag = 0;
        
        $accountName        = $request->get('account_name');
        $description        = $request->get('description');
        $accountType        = $request->get('account_type');
        $financialStatus    = $request->get('financial_status');
        $openingBalance     = $request->get('opening_balance');
        $name               = $request->get('name');
        $phone              = $request->get('phone');
        $address            = $request->get('address');
        $relation           = $request->get('relation_type');

        $openingBalanceAccount = Account::where('account_name','Account Opening Balance')->first();
        if(!empty($openingBalanceAccount) && !empty($openingBalanceAccount->id)) {
            $openingBalanceAccountId = $openingBalanceAccount->id;
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the account details. Try again after reloading the page!<small class='pull-right'> #01/01</small>")->with("alert-class","alert-danger");
        }

        $account = new Account;
        $account->account_name      = $accountName;
        $account->description       = $description;
        $account->type              = $accountType;
        $account->relation          = !empty($relation) ? $relation : ("Account Type : " . $accountType);
        $account->financial_status  = $financialStatus;
        $account->opening_balance   = $openingBalance;
        $account->status            = 1;
        if($account->save()) {
            $accountDetails = new AccountDetail;
            $accountDetails->account_id = $account->id;
            // account type of real/nominal accounts does not need to store personal data
            if($accountType == 3) {
                $accountDetails->name       = $name;
                $accountDetails->phone      = $phone;
                $accountDetails->address    = $address;
            } else {
                $accountDetails->name       = $accountName . " Account";
            }
            $accountDetails->status     = 1;
            if($accountDetails->save()) {
                if($financialStatus == 'debit') {//incoming [account holder gives cash to company] [Creditor]
                    $debitAccountId     = $openingBalanceAccountId;
                    $creditAccountId    = $account->id;
                    $particulars        = "Opening balance of ". $name . " - Debit [Creditor]";
                } else if($financialStatus == 'credit'){//outgoing [company gives cash to account holder] [Debitor]
                    $debitAccountId     = $account->id;
                    $creditAccountId    = $openingBalanceAccountId;
                    $particulars        = "Opening balance of ". $name . " - Credit [Debitor]";
                } else {
                    $debitAccountId     = $openingBalanceAccountId;
                    $creditAccountId    = $account->id;
                    $particulars        = "Opening balance of ". $name . " - None";
                }

                $dateTime = date('Y-m-d H:i:s', strtotime('now'));
                
                $transaction = new Transaction;
                $transaction->debit_account_id  = $debitAccountId;
                $transaction->credit_account_id = $creditAccountId;
                $transaction->amount            = !empty($openingBalance) ? $openingBalance : 0;
                $transaction->particulars       = $particulars;
                $transaction->status            = 1;
                $transaction->created_by        = Auth::user()->id;
                if($transaction->save()) {
                    $saveFlag = 1;
                } else {
                    //delete the account, account detail if opening balance transaction saving failed
                    $account->delete();
                    $accountDetails->delete();

                    $saveFlag = 2;
                }
            } else {
                //delete the account if account details saving failed
                $account->delete();

                $saveFlag = 3;
            }
        } else {
            $saveFlag = 4;
        }

        if($saveFlag == 1) {
            return redirect()->back()->with("message","Successfully saved.")->with("alert-class","alert-success");
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the account details. Try again after reloading the page!<small class='pull-right'> #01/02/". $saveFlag ."</small>")->with("alert-class","alert-danger");
        }

    }

    /**
     * Return view for account listing
     */
    public function list(Request $request)
    {
        $accountId  = !empty($request->get('account_id')) ? $request->get('account_id') : 0;
        $relation   = !empty($request->get('relation')) ? $request->get('relation') : '0';
        $type       = !empty($request->get('type')) ? $request->get('type') : '0';

        $accountsCombobox   = Account::where('status', '1')->get();

        $query = Account::where('status', '1');

        if(!empty($accountId) && $accountId != 0) {
            $query = $query->where('id', $accountId);
        }

        if(!empty($relation) && $relation != '0') {
            $query = $query->where('relation', $relation);
        }

        if(!empty($type) && $type != '0') {
            $query = $query->where('type', $type);
        }

        $accounts = $query->with('accountDetail')->orderBy('id','desc')->paginate(15);
        
        return view('account.list',[
                'accounts'          => $accounts,
                'accountsCombobox'  => $accountsCombobox,
                'accountId'         => $accountId,
                'relation'          => $relation,
                'type'              => $type
            ]);
    }

    /**
     * Return account name for given account id
     */
    public function getAccountDetailByAccountId($accountId)
    {
        $account = Account::where('id', $accountId)->first();
        if(!empty($account) && !empty($account->id)) {
            return ([
                    'flag' => true,
                    'name' => $account->accountDetail->name,
                ]);
        } else {
            return ([
                    'flag'      => false
                ]);            
        }
    }
}
