<?php

namespace Rutatiina\Contact\Http\Controllers;

use Illuminate\Validation\Rule;
use Rutatiina\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Rutatiina\Contact\Models\WithdrawRequest;
use Rutatiina\FinancialAccounting\Models\Account;
use Rutatiina\FinancialAccounting\Models\ContactBalance;
use Rutatiina\FinancialAccounting\Models\Txn;
use Rutatiina\FinancialAccounting\Models\TxnType;
use Rutatiina\Trading\Models\Order;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Contact\Models\ContactPerson;
use Rutatiina\Contact\Models\Comment;
use Rutatiina\Classes\Countries  as ClassesCountries;
use Rutatiina\Classes\Currencies  as ClassesCurrencies;
use Rutatiina\FinancialAccounting\Classes\Statement;
use Rutatiina\Tenant\Traits\TenantTrait;
use Rutatiina\FinancialAccounting\Classes\Transaction;
use Rutatiina\FinancialAccounting\Classes\Account as classAccount;

class AdministratorController extends Controller
{

    use TenantTrait;

    public function __construct()
    {
        $this->middleware('auth');

        //if (auth()->user()->hasRole('administrator')) {}
    }

    public function dashboard(Request $request)
    {
        //return view('lucid.h-menu.dashboard-administrator');
        return view('contact::dashboard_administrator')->with([
            'contactsCount' => Contact::count(),
            'buyOrdersCount' => Order::withTrashed()->where('type', 'buy')->count(),
            'sellOrdersCount' => Order::withTrashed()->where('type', 'sell')->count(),
            //'currencies' => ClassesCurrencies::en_IN(),
            //'countries' => ClassesCountries::ungrouped(),
        ]);
    }

}
