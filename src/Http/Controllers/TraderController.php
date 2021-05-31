<?php

namespace Rutatiina\Contact\Http\Controllers;

use Illuminate\Validation\Rule;
use App\User;
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
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Contact\Models\ContactPerson;
use Rutatiina\Contact\Models\Comment;
use Rutatiina\Contact\Models\User as ContactUser;
use Rutatiina\Classes\Countries as ClassesCountries;
use Rutatiina\Classes\Currencies as ClassesCurrencies;
use Rutatiina\FinancialAccounting\Classes\Statement;
use Rutatiina\Tenant\Traits\TenantTrait;
use Rutatiina\FinancialAccounting\Classes\Transaction;
use Rutatiina\FinancialAccounting\Classes\Account as classAccount;

class TraderController extends Controller
{

    use TenantTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $contacts = Contact::paginate(20);
        foreach($contacts as &$contact) {
            $contact->wallet_balance = classAccount::accountCode(7)->contactId($contact->id)->currency($contact->currency)->balanceByContact();
        }
        return view('contact::contacts')->with([
            'contacts' => $contacts
        ]);
    }

    public function topUp(Request $request)
    {

        if ($request->isMethod('get')) {
            return view('contact::admin.top_up')->with([
                'contacts' => Contact::all()
            ]);
        }

        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|numeric',
            'date' => 'date_format:"Y-m-d"|required',
            'currency' => 'required',
            'amount' => 'required|numeric',
            'description' => 'required|min:3|max:250',
            'reference' => 'required',
        ]);

        if ($validator->fails()) {
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }

        //create the invoice and receipt
        $walletTopUpCash = [
            'tenant_id'         => 1,
            'user_id'           => Auth::id(),

            //'contact_id'        => $request->contact_id,
            'items'             => [
                [
                    'name'          => 'Cash Wallet top up',
                    'description'   => $request->description,
                    'rate'          => $request->amount,
                    'quantity'      => 1,
                    'total'         => $request->amount,
                ]
            ],
            'txn_entree_name'   => $request->_method, //todo >> what ws i tryign to achieve here
            'base_currency'     => $request->currency,
            'date'              => $request->date, //date / date time in format YYYY-MM-DD HH:II:SS
            'recipient'         => [],
            'total'             => $request->amount,
            'on_success'        => null,
        ];

        $walletTopUpCashInsert = Transaction::contactById($request->contact_id)->insert($walletTopUpCash);

        if ($walletTopUpCashInsert == false) {
            return redirect()->back()->withErrors(Transaction::$rg_errors);
        }

        return redirect()->back()->with('success', 'Contact was successfully credited');

        /*/start db transaction
        $transaction = DB::transaction(function () use ($request) {

            //Debit the Escrow account
            $debit                 = new Debit;
            $debit->user_id        = $request->user_id;
            $debit->financial_account_code     = 3; //Escrow
            $debit->date           = $request->date;
            $debit->amount         = $request->amount;
            $debit->currency       = $request->currency;
            $debit->type           = 'Cash deposit';
            $debit->description    = $request->description;
            $debit->reference      = $request->reference;
            $debit->save();

            //Credit the users wallet
            $credit                 = new Credit;
            $credit->user_id        = $request->user_id;
            $credit->financial_account_code     = 1; //Wallet
            $credit->date           = $request->date;
            $credit->amount         = $request->amount;
            $credit->currency       = $request->currency;
            $credit->type           = 'Cash deposit';
            $credit->description    = $request->description;
            $credit->reference      = $request->reference;
            $credit->save();

            //update the balance
            $balance                = new Balance; //Balance::where('user_id', Auth::id())->first();
            $balance->user_id       = $request->user_id;
            $balance->credit        = $request->amount;// (!empty($balance->credit)) ? ($balance->credit + $request->amount) : $request->amount;
            $balance->debit         = 0;

            //end db transaction

            //return view('profile::credits-deposit')->with(['users'=> $users]);

            if ($balance->save()) {
                return redirect()->back()->with('success', 'User successfully credited');
            } else {
                return redirect()->back()->withErrors(['message'=>'An error occurred, please try again']);
            }

        });

        return $transaction;
        */
    }

    public function create(Request $request){

        /*
        if (auth()->user()->hasRole('administrator')) {
            if (auth()->user()->hasPermission('user.create')) {
                return 'has user.create permission';
            }

            //return 'This user is an administrator.';
            if (auth()->user()->hasAnyPermissions(['user.create', 'user.edit', 'user.destroy'])) {
                return 'This user either has create, edit or destroy permissions.';
            } else {
                return 'It looks like the user doesnt have any of the specified permissions.';
            }
        } else {
            return 'It looks like the user isnt an administrator.';
        }
        */

        if ($request->isMethod('get')) {
            return view('contact::admin.create')->with([
                'groups' => UserGroup::all(),
                'currencies' => ClassesCurrencies::en_IN(),
                'countries' => ClassesCountries::ungrouped(),
                //'countries_by_continent' => ClassesCountries::grouped_by_continent_regions(),
            ]);
        }

        if ($request->create_user_account == 'yes') {

            $user_data = [
                'first_name' => $request->first_name,
                'other_name' => $request->other_name,
                'email' => $request->user_email,
                'password' => $request->user_password,
                'password_confirmation' => $request->user_password,
            ];

            $validationRules['user_password'] = ['required', 'string', 'min:6', 'confirmed'];

            $validator = Validator::make($user_data, [
                'first_name' => ['required', 'string', 'max:255'],
                'other_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            if ($validator->fails()) {
                $request->flash();
                return redirect()->back()->withErrors($validator);
            }
        }

        $validationRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'other_name' => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'string', 'email'],
            'currency' => ['required', 'string'],
            'currencies'  => 'array',
            'country' => ['required', 'string'],
        ];


        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }

        $transaction = DB::transaction(function () use ($request) {

            $image = null;

            if ($request->file('image')) {
                $image = Storage::disk('public_storage')->putFile('/', $request->file('image'));
            }

            $contact                    = new Contact;
            $contact->tenant_id         = Auth::user()->tenant->id;
            $contact->category          = implode(',', $request->category);
            $contact->status            = 'active';
            $contact->image             = $image;
            $contact->first_name        = $request->first_name;
            $contact->other_name        = $request->other_name;
            $contact->display_name      = $request->display_name;
            $contact->currency          = $request->currency;
            $contact->currencies        = json_encode($request->currencies);
            $contact->country           = $request->country;
            $contact->payment_terms     = $request->payment_terms;

            $contact->facebook_link     = $request->facebook_link;
            $contact->twitter_link      = $request->twitter_link;
            $contact->remarks           = $request->remarks;

            if ($contact->contact_first_name) {
                $contact->contact_salutation    = $request->contact_salutation;
                $contact->contact_first_name    = $request->contact_first_name;
                $contact->contact_last_name     = $request->contact_last_name;
                $contact->contact_email         = $request->contact_email;
                $contact->contact_work_phone    = $request->contact_work_phone;
            } else {
                //$contact->contact_salutation    = $request->contact_salutation;
                $contact->contact_first_name    = $request->first_name;
                $contact->contact_last_name     = $request->other_name;
                $contact->contact_email         = $request->user_email;
                //$contact->contact_work_phone    = $request->contact_work_phone;
            }

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

            $contact_id = $contact->id;

            if ($request->create_user_account == 'yes') {

                $contactUser               = new ContactUser;
                $contactUser->tenant_id    = Auth::user()->tenant->id;
                $contactUser->contact_id   = $contact_id;
                $contactUser->name         = $request->first_name. ' '.$request->other_name;
                $contactUser->email        = $request->user_email;
                $contactUser->password     = bcrypt($request->user_password);
                $contactUser->save();

            }

            if ($request->contact_person) {
                foreach ($request->contact_person as $index => $person) {

                    if ($index == '_index_') continue;

                    //print_r($person); exit;

                    $contact_person                 = new ContactPerson;
                    $contact_person->tenant_id      = Auth::user()->tenant->id;
                    $contact_person->contact_id     = $contact_id;
                    $contact_person->salutation     = $person['salutation'];
                    $contact_person->first_name     = $person['first_name'];
                    $contact_person->last_name      = $person['last_name'];
                    $contact_person->email          = $person['email'];
                    $contact_person->work_phone     = $person['work_phone'];
                    $contact_person->mobile         = $person['mobile'];
                    $contact_person->save();
                }
            }


            return redirect()->back()->with(['success' => 'Contact created.']);

        });

        return $transaction;
    }

    public function update($id, Request $request) {

        /*
        if (auth()->user()->hasRole('administrator')) {
            if (auth()->user()->hasPermission('user.create')) {
                return 'has user.create permission';
            }

            //return 'This user is an administrator.';
            if (auth()->user()->hasAnyPermissions(['user.create', 'user.edit', 'user.destroy'])) {
                return 'This user either has create, edit or destroy permissions.';
            } else {
                return 'It looks like the user doesnt have any of the specified permissions.';
            }
        } else {
            return 'It looks like the user isnt an administrator.';
        }
        */


        //var_dump($contact->currencies); exit;

        if ($request->isMethod('get')) {

            $contact = Contact::find($id);

            $contact->currencies = (empty($contact->currencies)) ? [] : json_decode($contact->currencies, true);
            $contact->currencies = (is_array($contact->currencies)) ? $contact->currencies : [];

            //var_dump($contact->currencies); exit;

            return view('contact::admin.update')->with([
                'contact' => $contact,
                'groups' => UserGroup::all(),
                'currencies' => ClassesCurrencies::en_IN(),
                'countries' => ClassesCountries::ungrouped(),
                //'countries_by_continent' => ClassesCountries::grouped_by_continent_regions(),
            ]);
        }

        $validationRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'other_name' => ['required', 'string', 'max:255'],
            //'user_email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            //'user_password' => ['required', 'string', 'min:6', 'confirmed'],
            'currency' => ['required', 'string'],
            'currencies'  => 'array',
            'country' => ['required', 'string'],
        ];

        /*
        if ($request->create_user_account == 'yes') {
            $validationRules['user_password'] = ['required', 'string', 'min:6', 'confirmed'];
        }
        */

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }

        $transaction = DB::transaction(function () use ($request, $id) {


            /*
            $user_id = null;
            if ($request->create_user_account == 'yes') {
                //Create the user
                $user = User::create([
                    'name' => $request->name,
                    'user_email' => $request->user_email,
                    'user_group_id' => $request->user_group_id,
                    'user_password' => Hash::make($request->user_password),
                ]);

                $user_id = $user->id;
            }
            */

            $image = null;

            if ($request->file('image')) {
                $image = Storage::disk('public_storage')->putFile('/', $request->file('image'));
            }

            $contact                    = Contact::find($id);
            $contact->tenant_id         = Auth::user()->tenant->id;
            //$contact->user_id           = $user_id;
            $contact->category          = implode(',', $request->category);
            $contact->status            = 'active';
            $contact->image             = ($image)? $image : $contact->image;
            $contact->first_name        = $request->first_name;
            $contact->other_name        = $request->other_name;
            $contact->display_name      = $request->display_name;
            $contact->currency          = $request->currency;
            $contact->currencies        = json_encode($request->currencies);
            $contact->payment_terms     = $request->payment_terms;

            $contact->facebook_link     = $request->facebook_link;
            $contact->twitter_link      = $request->twitter_link;
            $contact->remarks           = $request->remarks;

            $contact->contact_salutation    = $request->contact_salutation;
            $contact->contact_first_name    = $request->contact_first_name;
            $contact->contact_last_name     = $request->contact_last_name;
            $contact->contact_email         = $request->contact_email;
            $contact->contact_work_phone    = $request->contact_work_phone;

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

            $contact_id = $contact->id;

            foreach ($request->contact_person as $index => $person) {

                if ($index == '_index_') continue;

                //print_r($person); exit;

                if ($index == '_1') {

                    $contact_person                 = new ContactPerson;
                    $contact_person->tenant_id      = Auth::user()->tenant->id;
                    $contact_person->contact_id     = $contact_id;
                    $contact_person->salutation     = $person['salutation'];
                    $contact_person->first_name     = $person['first_name'];
                    $contact_person->last_name      = $person['last_name'];
                    $contact_person->email          = $person['email'];
                    $contact_person->work_phone     = $person['work_phone'];
                    $contact_person->mobile         = $person['mobile'];
                    $contact_person->save();

                } else {

                    $contact_person                 = ContactPerson::find($index);
                    $contact_person->tenant_id      = Auth::user()->tenant->id;
                    $contact_person->contact_id     = $contact_id;
                    $contact_person->salutation     = $person['salutation'];
                    $contact_person->first_name     = $person['first_name'];
                    $contact_person->last_name      = $person['last_name'];
                    $contact_person->email          = $person['email'];
                    $contact_person->work_phone     = $person['work_phone'];
                    $contact_person->mobile         = $person['mobile'];
                    $contact_person->save();
                }
            }


            return redirect()->back()->with(['success' => 'Contact updated.']);

        });

        return $transaction;
    }

    public function contacts(Request $request) {

        if ($request->search) {
            $contacts = Contact::where('name', 'like', '%'.$request->search.'%')->paginate(15);
        } else {
            $contacts = Contact::paginate(15);
        }

        return view('contact::contacts')->with([
            'contacts' => $contacts,
        ]);
    }

    public function ____profile($contact_id = null, Request $request) {

        //return view('contact::profile');

        if (empty($contact_id)) {
            $contact = Contact::where('user_id', Auth::id())->first();
        } else {
            $contact = Contact::find($contact_id);
        }


        //Array of past 30 dates
        $dates = [];
        $walletBalances = [];
        $opening_date = date('Y-m-d', strtotime("-20 days", strtotime('now')));

        //print_r($opening_date); exit;

        $date_period = new \DatePeriod(
            new \DateTime($opening_date),
            new \DateInterval('P1D'),
            new \DateTime(date('Y-m-d', strtotime("+1 day", strtotime('now'))))
        );

        foreach($date_period as $date){
            classAccount::date($date->format("Y-m-d"))->accountCode(7)->contactId($contact->id)->currency($contact->currency)->balanceByContact()->returnModel();
            $dates[] = $date->format("Y-m-d");
            $walletBalances[$date->format("Y-m-d")] = classAccount::date($date->format("Y-m-d"))->accountCode(7)->contactId($contact->id)->currency($contact->currency)->balanceByContact();
        }

        //print_r($dates); exit;
        //print_r($walletBalances); exit;

        return view('contact::profile')->with([
            'walletBalance'    => classAccount::date(null)->accountCode(7)->contactId($contact->id)->currency($contact->currency)->balanceByContact(true),
            'contact'           => $contact,
            'walletBalances'    => $walletBalances,
            //'comments'          => Comment::where('contact_id', $contact_id)->where('tenant_id', Auth::user()->tenant->id)->orderBy('id', 'DESC')->get(),
            'dates'             => $dates
        ]);
    }

    //to be deleted
    public function ____edit(Request $request) {

        if ($request->isMethod('get')) {
            return view('contact::edit')->with(
                [
                    'contact' => Contact::where('user_id', Auth::id())->first(),
                    //'groups' => UserGroup::all(),
                    'currencies' => ClassesCurrencies::en_IN(),
                    'countries' => ClassesCountries::ungrouped(),
                ]
            );
        }

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
            return redirect()->back()->withErrors($validator);
        }

        $contact                        = Contact::where('user_id', Auth::id())->first();
        $contact->first_name            = $request->first_name;
        $contact->other_name            = $request->other_name;
        $contact->display_name          = $request->display_name;
        $contact->contact_salutation    = $request->contact_salutation;
        $contact->contact_first_name    = $request->contact_first_name;
        $contact->contact_last_name     = $request->contact_last_name;
        $contact->contact_email         = $request->contact_email;
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

    public function contact($contact_id = null, Request $request) {

        //return view('contact::profile');

        if (empty($contact_id)) {
            return redirect()->back()->withErrors(['message' => 'Error: Contact id is required.']);
        } else {
            $contact = Contact::find($contact_id);
        }

        //Array of past 30 dates
        $dates = array();
        $data_template = array();
        $opening_date = date('Y-m-d', strtotime("-30 days", strtotime('now')));

        //print_r($opening_date); exit;

        $date_period = new \DatePeriod(
            new \DateTime($opening_date),
            new \DateInterval('P1D'),
            new \DateTime(date('Y-m-d', strtotime("+1 day", strtotime('now'))))
        );

        foreach($date_period as $date){
            $dates[] = $date->format("Y-m-d");
            $data_template[$date->format("Y-m-d")] = 0;
        }

        //print_r($dates); exit;
        //print_r(Auth::user()->tenant); exit;

        //Get the contact balance for receviables & payables account for the given date range
        $contact_balances = ContactBalance::where('contact_id', $contact_id)
            ->where('tenant_id', Auth::user()->tenant->id)
            ->where('currency', Auth::user()->tenant->base_currency)
            ->whereIn('date', $dates)
            ->whereIn('financial_account_code', array(1,4))
            ->orderBy('date', 'DESC')
            ->get()
            ->toArray();
        //print_r($contact_balances); exit;

        $accounts = array();
        foreach($contact_balances as $contact_balance) {
            $accounts[$contact_balance['financial_account_code']]['id'] = $contact_balance['financial_account_code'];
        }

        if (empty($contact_balances)) {
            $financial_account_code = ( stripos($contact->category, 'supplier') !== false) ? 4 : 1;
            $accounts[$financial_account_code]['id'] = $financial_account_code;
        }
        //print_r($accounts); exit;

        //Get the name of the account
        foreach($accounts as $index => $account) {
            $account_row  = Account::find($account['id'])->toArray();

            if ( in_array($account_row['type'], array('inventory','cost_of_sales','none') ) ) {
                unset($accounts[$index]);
                continue;
            } else {
                $accounts[$index]['name'] = $account_row['name'];
                $accounts[$index]['type'] = $account_row['type'];
            }
        }

        foreach($accounts as $index => $account) {
            foreach($dates as $date) {
                $accounts[$index]['balance'][$date] = array(
                    'date' => $date,
                    'value' => 0
                );
            }
        }
        //print_r($accounts); exit;

        foreach($accounts as $index => $account) {
            foreach($account['balance'] as $account_balance) {
                foreach($contact_balances as $contact_balance) {
                    if ($account['id'] == $contact_balance['financial_account_code'] && $account_balance['date'] == $contact_balance['date']) {
                        if (in_array($account['type'], array('asset','expense','inventory') ) ) {
                            $balance = $contact_balance['debit'] - $contact_balance['credit'];
                        } else {
                            $balance = $contact_balance['credit'] - $contact_balance['debit'];
                        }

                        $accounts[$index]['balance'][$account_balance['date']]['value'] = $balance;
                    }
                }
            }
        }
        //print_r($accounts); exit;

        //*/

        //generate the Statement
        $financial_account_code = ( stripos($contact->category, 'supplier') !== false) ? 4 : 1; //print_r($parameters); exit;

        $Statement  = new Statement;
        $Statement->currency($this->tenant()->base_currency);
        $Statement->contactId($contact_id);
        $Statement->accountCode($financial_account_code);
        $Statement->openingDate($opening_date);
        $Statement->closingDate(date('Y-m-d'));
        $statement = $Statement->generate();

        //print_r($statement); exit;

        return view('contact::contact')->with([
            //'contacts'          => Contact::all(),
            'contact'           => $contact,
            'opening_date'      => $opening_date,
            'accounts_balance'  => $accounts,
            'statement'         => $statement,
            'comments'          => Comment::where('contact_id', $contact_id)->where('tenant_id', Auth::user()->tenant->id)->orderBy('id', 'DESC')->get(),
            'tenant'          => $this->tenant()
        ]);
    }

    /*
     * Admin viewing withdraw Requests
     */
    public function withdrawRequests(Request $request)
    {
        return view('contact::admin.withdraw_requests')->with([
            'withdrawRequests' => WithdrawRequest::paginate(20),
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);

        //return redirect()->back()->with(['success' => 'User details edited.']);
    }

    /*
     * admin updating / processing withdraw Requests
     */
    public function withdrawRequestsDeclined($idEncrypted, Request $request)
    {
        $id = Crypt::decryptString($idEncrypted);

        $withdrawRequest = WithdrawRequest::find($id);
        $withdrawRequest->status = 'declined';
        $withdrawRequest->save();

        return redirect()->back()->with(['success' => 'Withdraw Request DECLINED.']);
    }

    /*
     * admin updating / processing withdraw Requests
     */
    public function withdrawRequestsProcess($idEncrypted)
    {
        $withdrawRequest = WithdrawRequest::find(Crypt::decryptString($idEncrypted));
        $contact = Contact::find($withdrawRequest->contact_id);

        return view('contact::admin.withdraw_request_process')->with([
            'contact' => $contact,
            'withdrawRequest' => $withdrawRequest,
            'paymentAccounts' => classAccount::paymentAccounts(),
        ]);
    }

    /*
     * admin updating / processing withdraw Requests
     */
    public function withdrawRequestsApproved(Request $request)
    {
        $withdrawRequest = WithdrawRequest::find(Crypt::decryptString($request->id_encrypted));
        //$contact = Contact::where('user_id', Auth::id())->first();

        //return redirect('contacts/withdraw-requests')->with(['success' => 'Withdraw Request APPROVED.']);

        $transaction = [
            'tenant_id'         => Auth::user()->tenant->id,
            'user_id'           => Auth::id(),

            'debit'             => 7, //wallet
            'credit'            => $request->credit_financial_account_code,
            'reference'         => $request->reference,
            'base_currency'     => $withdrawRequest->currency,
            'date'              => date('Y-m-d'), //date / date time in format YYYY-MM-DD HH:II:SS

            'items' => [
                [
                    //'type'          => '',
                    //'type_id'       => $invoiceInsert->id,
                    'name'          => 'Withdraw request Approved #'.$withdrawRequest->id,
                    'description'   => $request->description,
                    'rate'          => $withdrawRequest->amount,
                    'quantity'      => 1,
                    'total'         => $withdrawRequest->amount,
                ],
            ],

            'total'             => $withdrawRequest->amount,
            'on_success'        => null,
        ];
        //print_r($receipt); exit;

        $transactionInsert = Transaction::contactById($withdrawRequest->contact_id)->insert($transaction);

        if ($transactionInsert == false) {
            return redirect()->back()->withErrors(Transaction::$rg_errors);
        }

        $withdrawRequest->status = 'approved';
        $withdrawRequest->process_reference = $request->reference;
        $withdrawRequest->process_txn_id = $transactionInsert->id;
        $withdrawRequest->save();

        //$withdrawRequest->delete();

        return redirect('contacts/withdraw-requests')->with(['success' => 'Withdraw Request APPROVED.']);
    }

}
