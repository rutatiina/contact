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
use Rutatiina\Asset\Models\Asset;
use Rutatiina\Asset\Models\AssetValuation;
use Rutatiina\FinancialAccounting\Models\Account;
use Rutatiina\FinancialAccounting\Models\ContactBalance;
use Rutatiina\FinancialAccounting\Models\Txn;
use Rutatiina\FinancialAccounting\Models\TxnType;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Contact\Models\ContactPerson;
use Rutatiina\Contact\Models\Comment;
use Rutatiina\Contact\Models\BankAccount;
use Rutatiina\Contact\Models\WithdrawRequest;
use Rutatiina\Trading\Models\Order;
use Rutatiina\Trading\Models\SecondaryShares;
use Rutatiina\Classes\Countries  as ClassesCountries;
use Rutatiina\Classes\Currencies  as ClassesCurrencies;
use Rutatiina\FinancialAccounting\Classes\Statement;
use Rutatiina\Tenant\Traits\TenantTrait;
use Rutatiina\FinancialAccounting\Classes\Transaction;
use Rutatiina\FinancialAccounting\Classes\Account as classAccount;

class ProfileController extends Controller
{
    use TenantTrait;

    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('auth:contact');
    }

    public function index()
    {
        return view('contact::contacts')->with([
            'contacts' => Contact::paginate(20)
        ]);
    }

    public function topUp()
	{
		$user_details_check = rg_contact_details_check();
        if ($user_details_check !== true) {
        	return $user_details_check;
		}

        $contact = Contact::find(Auth::user()->contact_id);

        return view('contact::top_up')->with([
            'contact'   => $contact,
			'currencies' => ClassesCurrencies::en_IN(),
			'countries' => ClassesCountries::ungrouped(),
        ]);
    }

    public function update(Request $request)
	{
        if ($request->isMethod('get')) {
            $contact = Contact::find(Auth::user()->contact_id);

            return view('contact::update')->with(
                [
                    'contact' => $contact,
                    //'groups' => UserGroup::all(),
                    'currencies' => ClassesCurrencies::en_IN(),
                    'countries' => ClassesCountries::ungrouped(),
                ]
            );
        }

        //print_r($request->contact_person); exit;

        $validationRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'other_name' => ['required', 'string', 'max:255'],
            //'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            //'password' => ['required', 'string', 'min:6', 'confirmed'],
            'currency' => ['required', 'string'],
            'currencies'  => 'array',
            'country' => ['required', 'string'],
        ];


        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }

        $contact                        = Contact::find(Auth::user()->contact_id);
        $contact->first_name            = $request->first_name;
        $contact->other_name            = $request->other_name;
        $contact->display_name          = $request->display_name;
        //$contact->contact_salutation    = $request->contact_salutation;
        //$contact->contact_first_name    = $request->contact_first_name;
        //$contact->contact_last_name     = $request->contact_last_name;
        //$contact->contact_email         = $request->contact_email;
        $contact->contact_work_phone    = $request->contact_work_phone;
        $contact->currency              = $request->currency;
        $contact->currencies            = json_encode($request->currencies);
        $contact->country               = $request->country;
        $contact->payment_terms         = $request->payment_terms;
        $contact->facebook_link         = $request->facebook_link;
        $contact->twitter_link          = $request->twitter_link;

        $contact->billing_address_attention     = $request->billing_address_attention;
        $contact->billing_address_street1       = $request->billing_address_street1;
        $contact->billing_address_street2       = $request->billing_address_street2;
        $contact->billing_address_city          = $request->billing_address_city;
        $contact->billing_address_state         = $request->billing_address_state;
        $contact->billing_address_zip_code      = $request->billing_address_zip_code;
        $contact->billing_address_country       = $request->billing_address_country;
        $contact->billing_address_fax           = $request->billing_address_fax;

        $contact->shipping_address_attention    = $request->shipping_address_attention;
        $contact->shipping_address_street1      = $request->shipping_address_street1;
        $contact->shipping_address_street2      = $request->shipping_address_street2;
        $contact->shipping_address_city         = $request->shipping_address_city;
        $contact->shipping_address_state        = $request->shipping_address_state;
        $contact->shipping_address_zip_code     = $request->shipping_address_zip_code;
        $contact->shipping_address_country      = $request->shipping_address_country;
        $contact->shipping_address_fax          = $request->shipping_address_fax;

        $contact->save();

        return redirect()->back()->with(['success' => 'User details edited.']);

    }

    private function contact($id)
    {
        if (empty($contact_id)) {
            //return Contact::where('user_id', Auth::id())->first();
            return Contact::find(Auth::user()->contact_id);
        } else {
            return Contact::find($contact_id);
        }
    }

    public function profile($contact_id = null, Request $request) {

        /*
        echo date('M- y:F:15'); // first month
        echo '<br>';
        echo date('M- y:F:15', strtotime('-1 month')); // previous month
        echo '<br>';
        echo date('M- y:F:15', strtotime('-2 month')); // second previous month
        echo '<br>';
        echo date('M- y:F:15', strtotime('-3 month')); // third previous month
        exit;
        //*/

        $contact = $this->contact($contact_id);

        //Array of past 30 dates
        $dates = [];
        $walletBalances = [];
        $opening_date = date('Y-m-d', strtotime("-1 month", strtotime('now')));

        //print_r($opening_date); exit;

        $date_period = new \DatePeriod(
            new \DateTime($opening_date),
            new \DateInterval('P1D'),
            new \DateTime( date('Y-m-d', strtotime("+1 day", strtotime('now'))))
        );

        foreach($date_period as $date){
            $dates[] = $date->format("Y-m-d");
            $walletBalances[$date->format("Y-m-d")] = classAccount::date($date->format("Y-m-d"))->accountCode(7)->contactId($contact->id)->currency($contact->currency)->balanceByContact();
        }

        //print_r($dates); exit;
        //print_r($walletBalances); exit;

        //get all asset ids of assets that belong to
        $asset_ids = [];
        $assets = Asset::select('id')->where('contact_id', $contact->id)->get();
        foreach ($assets as $asset) {
            $asset_ids[] = $asset->id;
        }

        $tradingSell = SecondaryShares::select(DB::raw('DATE(created_at)'), DB::raw('count(id) as count'), DB::raw('sum(quantity*rate) as total'))
            ->whereIn('asset_id', $asset_ids)
            ->where('contact_id', '!=', $contact->id)
            ->whereDate('created_at', '>=', $opening_date)
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()
            ->keyBy('DATE(created_at)');

        $tradingBuy = SecondaryShares::select(DB::raw('DATE(created_at)'), DB::raw('count(id) as count'), DB::raw('sum(quantity*rate) as total'))
            ->where('contact_id', $contact->id)
            ->whereDate('created_at', '>=', $opening_date)
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()
            ->keyBy('DATE(created_at)');

        //print_r($tradingSell); exit;
        //print_r($tradingBuy); exit;

        $offersPerDay = Order::withTrashed()
            ->select(DB::raw('DATE(created_at)'), DB::raw('count(id) as count'), DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'))
            ->where('type', 'sell')
            ->whereDate('created_at', '>=', $opening_date)
            ->groupBy('type', DB::raw("DATE(created_at)"))
            ->limit(30)
            ->get()
            ->keyBy('DATE(created_at)');

        $requestsPerDay = Order::withTrashed()
            ->select(DB::raw('DATE(created_at)'), DB::raw('count(id) as count'), DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'))
            ->where('type', 'buy')
            ->whereDate('created_at', '>=', $opening_date)
            ->groupBy('type', DB::raw("DATE(created_at)"))
            ->limit(30)
            ->get()
            ->keyBy('DATE(created_at)');

        $tradesPerDay = SecondaryShares::select(DB::raw('DATE(created_at)'), DB::raw('count(id) as count'), DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'))
            ->whereDate('created_at', '>=', $opening_date)
            ->groupBy(DB::raw("DATE(created_at)"))
            ->limit(30)
            ->get()
            ->keyBy('DATE(created_at)');

        $ordersPerDay = Order::withTrashed()
            ->select(DB::raw('DATE(created_at)'), DB::raw('count(id) as count'), DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'))
            ->whereDate('created_at', '>=', $opening_date)
            ->groupBy('type', DB::raw("DATE(created_at)"))
            ->limit(30)
            ->get()
            ->keyBy('DATE(created_at)');

        //print_r($ordersPerDay); exit;

        return view('contact::profile')->with([
            'walletBalance'    => classAccount::date(null)->accountCode(7)->contactId($contact->id)->currency($contact->currency)->balanceByContact(true),
            'contact'           => $contact,
            'walletBalances'    => $walletBalances,
            //'comments'          => Comment::where('contact_id', $contact_id)->where('tenant_id', Auth::user()->tenant->id)->orderBy('id', 'DESC')->get(),
            'dates'             => $dates,
            'tradingSell'       => $tradingSell,
            'tradingBuy'        => $tradingBuy,
            'offersPerDay'      => $offersPerDay,
            'requestsPerDay'    => $requestsPerDay,
            'tradesPerDay'      => $tradesPerDay,
            'ordersPerDay'      => $ordersPerDay
        ]);
    }

    public function valuation($contact_id = null, Request $request)
    {
        $contact = $this->contact($contact_id);

        return view('contact::profile')->with([
            'contact' => $contact,
            'valuations' => AssetValuation::where('contact_id', $contact->id)->paginate(10),
        ]);
    }

    public function transactions($contact_id = null, Request $request)
    {
        $contact = $this->contact($contact_id);

        return view('contact::profile')->with([
            'contact' => $contact,
            'transactions' => Transaction::contactByUserId()->findByAccount(7), //Wallet
        ]);
    }

    public function imageUpdate(Request $request)
    {
        if ($request->file('image')) {
            $image              = Storage::disk('public_storage')->putFile('/', $request->file('image'));
            $contact            = Contact::find(Auth::user()->contact_id);
            $contact->image     = $image;
            $contact->save();

            return $image;
        }

        return false;

        //return redirect()->back()->with(['success' => 'User details edited.']);
    }

    public function bankAccounts(Request $request)
    {
        $contact = Contact::find(Auth::user()->contact_id);

        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), [
                'country' => ['required', 'string', 'max:5'],
                'bank' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'number' => ['required', 'string', 'min:3'],
                'currency' => ['required', 'string'],
                //'description' => ['string'],
            ]);

            if ($validator->fails()) {
                $request->flash();
                return redirect()->back()->withErrors($validator);
            }

            $bankAccount                = new BankAccount;
            $bankAccount->tenant_id     = $contact->tenant_id;
            $bankAccount->contact_id    = $contact->id;
            $bankAccount->country       = $request->country;
            $bankAccount->bank          = $request->bank;
            $bankAccount->name          = $request->name;
            $bankAccount->number        = $request->number;
            $bankAccount->currency      = $request->currency;
            $bankAccount->description   = $request->description;
            $bankAccount->save();

            return redirect()->back()->with(['success' => 'Bank account saved.']);

        }

        return view('contact::bank_accounts')->with([
            'bankAccounts' => BankAccount::where('contact_id', $contact->id)->paginate(20),
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);

        //return redirect()->back()->with(['success' => 'User details edited.']);
    }

    public function withdrawRequests(Request $request)
    {
        $contact = Contact::find(Auth::user()->contact_id);

        if ($request->isMethod('post')) {

            $walletBalance = classAccount::date(null)->accountCode(7)->contactId(Auth::user()->contact_id)->currency($contact->currency)->balanceByContact();

            $validator = Validator::make($request->all(), [
                'amount' => ['required', 'integer', 'gt:0', 'lt:'.$walletBalance],
                'currency' => ['required', 'string', 'max:255'],
                'bank_account_id' => ['required', 'string', 'max:255'],
                //'description' => ['string'],
            ]);

            if ($validator->fails()) {
                $request->flash();
                return redirect()->back()->withErrors($validator);
            }

            $withdrawRequest                    = new WithdrawRequest;
            $withdrawRequest->tenant_id         = $contact->tenant_id;
            $withdrawRequest->contact_id        = $contact->id;
            $withdrawRequest->amount            = $request->amount;
            $withdrawRequest->currency          = $request->currency;
            $withdrawRequest->bank_account_id   = $request->bank_account_id;
            $withdrawRequest->description       = $request->description;
            $withdrawRequest->status            = 'Pending';
            $withdrawRequest->save();

            return redirect()->back()->with(['success' => 'Withdraw Request initiated.']);

        }

        return view('contact::withdraw_requests')->with([
            'withdrawRequests' => WithdrawRequest::where('contact_id', $contact->id)->paginate(20),
            'bankAccounts' => BankAccount::where('contact_id', $contact->id)->get(),
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);

        //return redirect()->back()->with(['success' => 'User details edited.']);
    }

}
