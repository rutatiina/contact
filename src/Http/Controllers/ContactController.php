<?php

namespace Rutatiina\Contact\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Rutatiina\Bill\Models\Bill;
use Rutatiina\Contact\Models\Comment;
use Rutatiina\FinancialAccounting\Traits\Forex;
use Rutatiina\Invoice\Models\Invoice;
use Rutatiina\SalesOrder\Models\SalesOrder;
use Rutatiina\Tax\Models\Tax;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Tenant\Traits\TenantTrait;
use Yajra\DataTables\Facades\DataTables;
use Rutatiina\Globals\Services\Countries as ClassesCountries;
use Rutatiina\Globals\Services\Currencies as ClassesCurrencies;
use Maatwebsite\Excel\Facades\Excel;
use Rutatiina\FinancialAccounting\Models\Account;
use Rutatiina\FinancialAccounting\Models\ContactBalance;
use Illuminate\Support\Str;

use Rutatiina\Contact\Classes\Store as ContactStore;
use Rutatiina\Contact\Classes\Update as ContactUpdate;

class ContactController extends Controller
{
    use Forex;

    protected $contact_id = null;
    protected $financial_account_code = null;
    protected $opening_date = null;
    protected $closing_date = null;
    protected $currency = null;

    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('permission:contacts.view');
        $this->middleware('permission:contacts.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:contacts.update', ['only' => ['edit', 'update']]);
        $this->middleware('permission:contacts.delete', ['only' => ['destroy']]);

        $this->opening_date = date('Y-m-d', strtotime("-30 days", strtotime('now')));
        $this->closing_date = date('Y-m-d');
    }

    public function index(Request $request)
    {
        //return view('contact.limitless::index');
   
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $query = Contact::query();

        if ($request->search)
        {
            $query->where(function($q) use ($request) {
                $columns = (new Contact)->getSearchableColumns();
                foreach($columns as $column)
                {
                    $q->orWhere($column, 'like', '%'.Str::replace(' ', '%', $request->search).'%');
                }
            });
        }

        $query->latest();
        $Contacts = $query->paginate(15);

        return [
            'tableData' => $Contacts
        ];
    }

    public function create()
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $user = Auth::user();
        $tenant = $user->tenant;

        $contact = new Contact;
        $attributes = $contact->rgGetAttributes();

        $attributes['types'][] = 'customer';
        $attributes['country'] = $tenant->country;
        $attributes['currency'] = $tenant->base_currency;
        $attributes['currencies'] = [$tenant->base_currency];
        $attributes['_method'] = 'POST';

        $data = [
            'pageTitle' => 'Create Contact',
            'urlPost' => '/contacts', #required
            'routes' => [
                'store' => route('contacts.store'),
                'deactivate' => route('contacts.deactivate'),
                'activate' => route('contacts.activate'),
            ],
            'types' => ['customer', 'supplier', 'salesperson', 'agent'],
            //'currencies' => ClassesCurrencies::en_INSelectOptions(),
            //'countries' => ClassesCountries::ungroupedSelectOptions(),
            //'taxes' => Tax::all(),
            'attributes' => $attributes,
            'selectedCurrencies' => [],
            'selectedTaxes' => [],
        ];

        if (FacadesRequest::wantsJson())
        {
            return $data;
        }

        return view('contact.limitless::create')->with($data);
    }

    public function store(Request $request)
    {
        $ContactStore = new ContactStore;
        $store = $ContactStore->run($request);

        if ($store)
        {
            return [
                'status' => true,
                'messages' => ['Contact saved'],
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => $ContactStore->errors,
            ];
        }
    }

    public function show($id)
    {
        $contact = Contact::findOrFail($id);

        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $statistics = [
            'routes' => [
                'deactivate' => route('contacts.deactivate'),
                'activate' => route('contacts.activate'),
            ],
            'invoices' => Invoice::where('contact_id', $contact->id)->count(),
            'bills' => Bill::where('contact_id', $contact->id)->count(),
            'orders' => SalesOrder::where('contact_id', $contact->id)->count(),
        ];

        $contact->statistics = $statistics;

        return [
            'routes' => [
                'destroy' => route('contacts.destroy', ['id', $contact->id]),
                'deactivate' => route('contacts.deactivate'),
                'activate' => route('contacts.activate'),
            ],
            'contact' => $contact,
        ];

    }

    public function edit($id)
    {
        //return $id;

        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $currencies = ClassesCurrencies::en_IN(); //return $currencies;
        $taxes = Tax::all();
        $taxesKeyById = $taxes->keyBy('id');

        $attributes = Contact::with('contact_persons')->find($id);
        //$attributes = $contact->rgGetAttributes();

        //$attributes['types'][] = 'customer';

        $selectedCurrencies = [];
        $selectedTaxes = [];

        foreach ($attributes->currencies as $value)
        {
            $selectedCurrencies[] = [
                'value' => $value,
                'text' => $value.' - '.$currencies[$value]
            ];
        }

        foreach ($attributes->taxes as $value)
        {
            if ($value == 'all')
            {
                $selectedTaxes = $taxes;
                break;
            }
            $selectedTaxes[] = $taxesKeyById[$value];
        }

        $attributes->_method = 'PATCH';

        $data = [
            'pageTitle' => 'Update Contact',
            'urlPost' => '/contacts/' . $attributes->id, #required
            //'currencies' => ClassesCurrencies::en_INSelectOptions(),
            //'countries' => ClassesCountries::ungroupedSelectOptions(),
            //'taxes' => $taxes,
            'attributes' => $attributes,
            'selectedCurrencies' => $selectedCurrencies,
            'selectedTaxes' => $selectedTaxes,
        ];

        if (FacadesRequest::wantsJson())
        {
            return $data;
        }
    }

    public function update($id, Request $request)
    {
        $ContactUpdate = new ContactUpdate;
        $update = $ContactUpdate->run($request);

        if ($update)
        {
            return [
                'status' => true,
                'messages' => ['Contact updated'],
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => $ContactUpdate->errors,
            ];
        }
    }

    public function destroy($id)
    {
        //deactivate the contact
        $contact = Contact::find($id);
        $contact->status = 'inactive';
        $contact->save();

        #ckeck if contact is attached to any sales transactions

        //estimates
        if (class_exists(\Rutatiina\Estimate\Models\Estimate::class) && \Rutatiina\Estimate\Models\Estimate::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Estimate and thus cannot be deleted but only deactivated.'],
            ];
        }

        //retainer invoices
        if (class_exists(\Rutatiina\RetainerInvoice\Models\RetainerInvoice::class) && \Rutatiina\RetainerInvoice\Models\RetainerInvoice::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Retainer Invoice and thus cannot be deleted but only deactivated.'],
            ];
        }

        //sales orders
        if (class_exists(\Rutatiina\SalesOrder\Models\SalesOrder::class) && \Rutatiina\SalesOrder\Models\SalesOrder::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Sales Order and thus cannot be deleted but only deactivated.'],
            ];
        }

        //invoices
        if (class_exists(\Rutatiina\Invoice\Models\Invoice::class) && \Rutatiina\Invoice\Models\Invoice::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Invoice and thus cannot be deleted but only deactivated.'],
            ];
        }

        //payment received
        if (class_exists(\Rutatiina\PaymentReceived\Models\PaymentReceived::class) && \Rutatiina\PaymentReceived\Models\PaymentReceived::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Payment Received and thus cannot be deleted but only deactivated.'],
            ];
        }

        //recurring invoices
        if (class_exists(\Rutatiina\Invoice\Models\RecurringInvoice::class) && \Rutatiina\Invoice\Models\RecurringInvoice::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Recurring Invoice and thus cannot be deleted but only deactivated.'],
            ];
        }

        //credit notes
        if (class_exists(\Rutatiina\CreditNote\Models\CreditNote::class) && \Rutatiina\CreditNote\Models\CreditNote::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Credit Note and thus cannot be deleted but only deactivated.'],
            ];
        }


        #ckeck if contact is attached to any purchases transactions

        //expenses
        if (class_exists(\Rutatiina\Expense\Models\Expense::class) && \Rutatiina\Expense\Models\Expense::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Expense and thus cannot be deleted but only deactivated.'],
            ];
        }

        //recurring expenses
        if (class_exists(\Rutatiina\Expense\Models\RecurringExpense::class) && \Rutatiina\Expense\Models\RecurringExpense::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Recurring expense and thus cannot be deleted but only deactivated.'],
            ];
        }

        //purchase orders
        if (class_exists(\Rutatiina\PurchaseOrder\Models\PurchaseOrder::class) && \Rutatiina\PurchaseOrder\Models\PurchaseOrder::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Purchase Order and thus cannot be deleted but only deactivated.'],
            ];
        }

        //bills
        if (class_exists(\Rutatiina\Bill\Models\Bill::class) && \Rutatiina\Bill\Models\Bill::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Bill and thus cannot be deleted but only deactivated.'],
            ];
        }

        //payment made
        if (class_exists(\Rutatiina\PaymentMade\Models\PaymentMade::class) && \Rutatiina\PaymentMade\Models\PaymentMade::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Payment Made and thus cannot be deleted but only deactivated.'],
            ];
        }

        //recurring bill
        if (class_exists(\Rutatiina\Bill\Models\RecurringBill::class) && \Rutatiina\Bill\Models\RecurringBill::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Recurring Bill and thus cannot be deleted but only deactivated.'],
            ];
        }

        //debit notes
        if (class_exists(\Rutatiina\DebitNote\Models\DebitNote::class) && \Rutatiina\DebitNote\Models\DebitNote::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Debit Note and thus cannot be deleted but only deactivated.'],
            ];
        }

        //Journal entry
        if (class_exists(\Rutatiina\JournalEntry\Models\JournalEntryRecording::class) && \Rutatiina\JournalEntry\Models\JournalEntryRecording::where('contact_id', $id)->first())
        {
            return [
                'status' => false,
                'messages' => ['Contact is attached to an Journal entry and thus cannot be deleted but only deactivated.'],
            ];
        }

        //if all the bove conditions are passed: Delete the contact
        $contact->comments()->delete();
        $contact->contact_persons()->delete();
        $contact->address_book()->delete();
        $contact->delete();

        return [
            'status' => true,
            'messages' => ['Contact deleted.'],
            'callback' => '/contacts',
        ];

    }

    public function search(Request $request)
    {
        $query = Contact::query();
        $query->where('status', 'active');

        foreach ($request->search as $search)
        {
            $query->where($search['column'], 'like', '%' . $search['value'] . '%');
        }
        $paginate = $query->orderBy('name', 'asc')->paginate(20);

        //print_r($paginate); exit;
        //print_r($paginate->toArray()); exit;

        foreach ($paginate as $key => $contact)
        {
            $paginate[$key] = [
                'id' => $contact->id,
                'tenant_id' => $contact->tenant_id,
                'display_name' => $contact->display_name,
                'currencies' => $contact->currencies_and_exchange_rates,
                'currency' => $contact->currency_and_exchange_rate,
            ];
        }

        return $paginate;
    }

    public function searchSalesPersons(Request $request)
    {
        $query = Contact::query();
        $query->whereJsonContains('types', 'salesperson');
        //$query->where('types', 'like', '%salesperson%');

        foreach ($request->search as $search)
        {
            $query->where($search['column'], 'like', '%' . $search['value'] . '%');
        }
        $paginate = $query->orderBy('name', 'asc')->paginate(20);

        //print_r($paginate); exit;
        //print_r($paginate->toArray()); exit;

        foreach ($paginate as $key => $contact)
        {
            $paginate[$key] = [
                'id' => $contact->id,
                'tenant_id' => $contact->tenant_id,
                'display_name' => $contact->display_name,
                'currencies' => $contact->currencies_and_exchange_rates,
                'currency' => $contact->currency_and_exchange_rate,
            ];
        }

        return $paginate;
    }

    public function datatables()
    {
        return Datatables::of(Contact::query())->make(true);
    }

    public function statement($id)
    {
        $contact = Contact::find($id);

        $request = request();

        //print_r($this->input->post()); exit;
        $financial_account_code = (in_array('supplier', $contact->types) !== false) ? 4 : 1; //print_r($parameters); exit;
        $opening_date = $this->opening_date;
        $closing_date = $this->closing_date;
        $currency = $this->currency;

        /*
        print_r([
            'financial_account_code' => $financial_account_code,
            'opening_date' => $opening_date,
            'closing_date' => $closing_date,
            'currency' => $currency,
        ]);
        exit;
        //*/

        $statement = [];

        $statement['opening_date'] = $opening_date;
        $statement['closing_date'] = $closing_date;
        $statement['currency'] = $currency;

        //Get the opening balance (Blance of the day before)
        $query = ContactBalance::query();
        $query->where('contact_id', $contact->id);
        $query->where('financial_account_code', $financial_account_code);
        $query->where('tenant_id', Auth::user()->tenant->id);
        $query->where('currency', $currency);
        $query->where('date', '<', $opening_date);
        $query->orderBy('date', 'desc');
        $row = $query->first();

        if ($row)
        {
            $row = $row->toArray();
        }
        else
        {
            $row['credit'] = $row['debit'] = 0;
        }

        //print_r($row); exit;

        $account = Account::findCode($financial_account_code)->toArray();
        $statement['account'] = $account;

        if (in_array($account['type'], ['equity', 'income', 'liability']))
        {
            $balance = $row['credit'] - $row['debit'];
        }
        else
        {
            $balance = $row['credit'] - $row['debit'];
        }

        $statement['opening_balance'] = $balance;

        $statement['data'][] = array(
            'date' => $opening_date,
            'name' => 'Opening balance',
            'number' => '',
            'reference' => '',
            'debit' => floatval($row['debit']),
            'credit' => floatval($row['credit']),
            'balance' => floatval($balance),
        );


        $query = Txn::query();
        $query->where('contact_id', $contact->id);
        $query->where('tenant_id', Auth::user()->tenant->id);
        $query->where('base_currency', $currency);
        $query->where('date', '>=', $opening_date);
        $query->where('date', '<=', $closing_date);
        $query->where(function ($query) use ($financial_account_code)
        {
            $query->where('debit', $financial_account_code);
            $query->orWhere('credit', $financial_account_code);
        });
        $query->orderBy('date', 'ASC');
        $query->orderBy('id', 'ASC');
        $rows = $query->get()->toArray();

        //print_r($rows); exit;
        //print_r($account); exit;

        foreach ($rows as $row)
        {

            $debit = ($financial_account_code == $row['debit']) ? $row['total'] : 0;
            $credit = ($financial_account_code == $row['credit']) ? $row['total'] : 0;

            if (in_array($account['type'], array('equity', 'income', 'liability')))
            {
                $balance += $credit - $debit;
            }
            else
            {
                $balance += $debit - $credit;
            }

            $txn_type = TxnType::find($row['txn_type_id'])->toArray();

            $statement['data'][] = array(
                'date' => date('Y-m-d', strtotime($row['date'])),
                'name' => $txn_type['name'],
                'number' => $row['number'],
                'reference' => $row['reference'],
                'debit' => floatval($debit),
                'credit' => floatval($credit),
                'balance' => floatval($balance),
            );
        }

        //Get the Closing balance
        $query = ContactBalance::query();
        $query->where('contact_id', $contact->id);
        $query->where('financial_account_code', $financial_account_code);
        $query->where('tenant_id', Auth::user()->tenant->id);
        $query->where('currency', $currency);
        $query->where('date', '>=', $closing_date);
        $query->orderBy('date', 'desc');
        $row = $query->first();

        if ($row)
        {
            $row = $row->toArray();
        }
        else
        {
            $row['credit'] = $row['debit'] = 0;
        }

        //print_r($row); exit;

        $account = Account::findCode($financial_account_code)->toArray();
        $statement['account'] = $account;

        if (in_array($account['type'], array('equity', 'income', 'liability')))
        {
            $balance += $row['credit'] - $row['debit'];
        }
        else
        {
            $balance += $row['credit'] - $row['debit'];
        }

        $statement['closing_balance'] = $balance;

        $statement['data'][] = array(
            'date' => $closing_date,
            'name' => 'Closing balance',
            'number' => '',
            'reference' => '',
            'debit' => floatval($row['debit']),
            'credit' => floatval($row['credit']),
            'balance' => floatval($balance),
        );

        return view('contact.limitless::show.statement')->with([
            'opening_date' => $opening_date,
            'statement' => $statement,
            'contacts' => Contact::all(),
            'contact' => $contact,
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);

    }

    public function sales($id)
    {
        $contact = Contact::find($id);
        return view('contact.limitless::show.sales')->with([
            'contacts' => Contact::all(),
            'contact' => $contact,
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);
    }

    public function purchases($id)
    {
        $contact = Contact::find($id);
        return view('contact.limitless::show.purchases')->with([
            'contacts' => Contact::all(),
            'contact' => $contact,
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);
    }

    public function remarks($id)
    {
        $contact = Contact::find($id);
        return view('contact.limitless::show.remarks')->with([
            'contacts' => Contact::all(),
            'contact' => $contact,
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);
    }

    public function mails($id)
    {
        $contact = Contact::find($id);
        return view('contact.limitless::show.mails')->with([
            'contacts' => Contact::all(),
            'contact' => $contact,
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);
    }

    public function comments($id)
    {
        $contact = Contact::find($id);
        return view('contact.limitless::show.comments')->with([
            'contacts' => Contact::all(),
            'contact' => $contact,
            'currencies' => ClassesCurrencies::en_IN(),
            'countries' => ClassesCountries::ungrouped(),
        ]);
    }

    public function import(Request $request)
    {
        $allowed_file_type = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', //xlsx
            'application/vnd.ms-excel',
            'text/plain',
            'text/csv',
            'text/tsv'
        ];

        if (in_array($request->file->getClientMimeType(), $allowed_file_type))
        {
            // do nothing i.e allow file processing
        }
        else
        {
            $response = [
                'status' => false,
                'message' => 'ERROR: File type not allowed / mime type not allowed.'
            ];
            return json_encode($response);
        }

        //print_r($this->input->post()); exit;

        //Save the uploaded file
        $importFile = Storage::disk('public_storage')->putFile('/', $request->file('file'));


        //Copy imported file into array
        //$params = ['io_factory' => true];
        //$this->load->library('third_party_phpexcel', $params);

        //$data = Excel::toCollection($request->file('file'), 'storage/'.$importFile);
        $excelToArray = Excel::toArray($request->file('file'), 'storage/' . $importFile);
        //dd($excelToArray);
        //print_r($excelToArray[0]);

        $data = $excelToArray[0];

        //print_r($data); exit;
        unset($data[0]); //delete the 1st line of titles

        /*
            Check for error within the file
            [A] => Category
            [B] => Full name
            [C] => Display name
            [D] => Contact person
            [E] => Contact Email
            [F] => Contact phone
            [G] => Payment terms
            [H] => Remarks
        */

        $responseMessage = null;
        $contacts = [];
        foreach ($data as $key => $value)
        {

            $row = [
                'types' => json_encode([$value[0]]),
                'first_name' => $value[1],
                'other_name' => $value[2],
                'display_name' => $value[3],
                'contact_email' => $value[4],
                'contact_work_phone' => $value[5],
                'contact_mobile' => $value[6],
                'payment_terms' => $value[7],
                'remarks' => $value[8],
                'tenant_id' => Auth::user()->tenant->id,
                'currency' => Auth::user()->tenant->base_currency,
            ];

            $validator = Validator::make($row, [
                'types' => ['required'],
                'first_name' => ['required', 'string', 'min:2', 'max:255'],
                'other_name' => ['required', 'string', 'max:255'],
                'contact_email' => ['required', 'email'],
            ]);

            if ($validator->fails())
            {
                foreach ($validator->errors()->all() as $field => $messages)
                {
                    $responseMessage .= "\n" . $messages;
                }
                $response = [
                    'status' => false,
                    'message' => 'Error on row #' . ($key + 1) . $responseMessage,
                ];

                return json_encode($response);

            }
            else
            {
                $contacts[] = $row;
            }

        }

        //print_r($contacts); exit;

        Contact::insert($contacts);

        $response = [
            'status' => true,
            'message' => count($contacts) . ' Contact(s) imported.' . "\n" . $responseMessage,
        ];

        return json_encode($response);
    }

    public function deactivate(Request $request)
    {
        Contact::whereIn('id', $request->ids)->update(['status' => 'inactive']);

        $response = [
            'status' => true,
            'messages' => [count($request->ids) . ' Contact(s) deactivated.'],
            'contact' => [
                'status' => 'inactive'
            ]

        ];

        return json_encode($response);
    }

    public function activate(Request $request)
    {
        Contact::whereIn('id', $request->ids)->update(['status' => 'active']);

        $response = [
            'status' => true,
            'messages' => [count($request->ids) . ' Contact(s) activated.'],
            'contact' => [
                'status' => 'active'
            ]
        ];

        return json_encode($response);
    }

    public function delete(Request $request)
    {
        Contact::whereIn('id', $request->ids)->delete();

        $response = [
            'status' => true,
            'messages' => count($request->ids) . ' Contact(s) deleted.',
        ];

        return json_encode($response);
    }

    public function routes()
    {
        return [
            'delete' => route('contacts.delete'),
            'activate' => route('contacts.activate'),
            'deactivate' => route('contacts.deactivate'),
        ];
    }
}
