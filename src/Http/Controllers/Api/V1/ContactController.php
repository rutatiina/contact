<?php

namespace Rutatiina\Contact\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Contact\Models\ContactPerson;
use Rutatiina\Tenant\Traits\TenantTrait;
use Yajra\DataTables\Facades\DataTables;
use Rutatiina\Globals\Services\Countries as ClassesCountries;
use Rutatiina\Globals\Services\Currencies as ClassesCurrencies;
use Maatwebsite\Excel\Facades\Excel;
use Rutatiina\FinancialAccounting\Models\Txn;
use Rutatiina\FinancialAccounting\Models\TxnType;
use Rutatiina\FinancialAccounting\Models\Account;
use Rutatiina\FinancialAccounting\Models\ContactBalance;

class ContactController extends Controller
{

    public function __construct()
    {}

    /*
     * 1st priority is to check id
     * 2nd priority is to check external_key
     * so 1st id then external_key
     */
    private function contact($id)
	{
		if(is_numeric($id)) {
			$Contact = Contact::find($id);
			if ($Contact) {
				return $Contact;
			}
		}

		$query = Contact::where('external_key', $id);
        $count = $query->count();

		if (!$count) {
			response()->json([
				'status' => 'error',
				'data' => [],
				'messages' => ['Record not found'],
			])->send();
			exit;
		}

		if ($count > 1) {
			response()->json([
				'status' => 'error',
				'data' => [],
				'messages' => ['Multiple records found'],
			])->send();
			exit;
		}

		return $query->first();
	}

    public function index()
    {
        return [
			'status' => 'success',
			'data' => Contact::all(),
			'messages' => []
		];
    }

    public function create()
    {
        return [
			'status' => 'error',
			'data' => [],
			'messages' => ['Unknown request (create)'],
		];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        	'external_key' => ['max:100', 'unique:rg_contact_contacts'],
            'types' => ['required', 'array'],
            'first_name' => ['required', 'string', 'max:255'],
            'other_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string'],
            'currencies' => 'array',
            'country' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $response = ['status' => 'error', 'data' => [], 'messages' => []];
            foreach ($validator->errors()->all() as $field => $message) {
                $response['messages'][] = $message;
            }
            return $response;
        }

        DB::beginTransaction();

        try {

            $image = null;

            if ($request->file('image')) {
                $image = Storage::disk('public_storage')->putFile('/', $request->file('image'));
            }

            if ($request->display_name) {
            	$displayName = $request->display_name;
			} else {
            	$displayName = trim($request->salutation.' '.$request->first_name.' '.$request->other_name);
			}

            $contact = new Contact;
            $contact->tenant_id = Auth::user()->tenant->id;
            $contact->external_key = $request->external_key;
            $contact->types = json_encode($request->types);
            $contact->status = 'active';
            $contact->image = $image;
            $contact->salutation = ($request->salutation == 'none') ? '' : $request->salutation;
            $contact->first_name = $request->first_name;
            $contact->other_name = $request->other_name;
            $contact->display_name = $displayName;
            $contact->currency = $request->currency;
            $contact->currencies = json_encode($request->currencies);
            $contact->country = $request->country;
            $contact->payment_terms = $request->payment_terms;

            $contact->facebook_link = $request->facebook_link;
            $contact->twitter_link = $request->twitter_link;
            $contact->remarks = $request->remarks;

            $contact->contact_salutation = ($request->contact_salutation ? $request->contact_salutation : $request->salutation);
            $contact->contact_first_name = ($request->contact_first_name ? $request->contact_first_name : $request->first_name);
            $contact->contact_last_name = ($request->contact_last_name ? $request->contact_last_name : $request->last_name);
            $contact->contact_email = ($request->contact_email ? $request->contact_email : $request->email);
            $contact->contact_work_phone = $request->contact_work_phone;

            $contact->billing_address_attention = $request->billing_address_attention;
            $contact->billing_address_street1 = $request->billing_address_street1;
            $contact->billing_address_street2 = $request->billing_address_street2;
            $contact->billing_address_city = $request->billing_address_city;
            $contact->billing_address_state = $request->billing_address_state;
            $contact->billing_address_zip_code = $request->billing_address_zip_code;
            $contact->billing_address_country = $request->billing_address_country;
            $contact->billing_address_fax = $request->billing_address_fax;

            $contact->shipping_address_attention = $request->shipping_address_attention;
            $contact->shipping_address_street1 = $request->shipping_address_street1;
            $contact->shipping_address_street2 = $request->shipping_address_street2;
            $contact->shipping_address_city = $request->shipping_address_city;
            $contact->shipping_address_state = $request->shipping_address_state;
            $contact->shipping_address_zip_code = $request->shipping_address_zip_code;
            $contact->shipping_address_country = $request->shipping_address_country;
            $contact->shipping_address_fax = $request->shipping_address_fax;

            $contact->save();

            $contact_id = $contact->id;


            if ($request->contact_person) {
                foreach ($request->contact_person as $index => $person) {

                    if ($index == '_index_') continue;

                    //print_r($person); exit;

                    if (empty($person->first_name) || empty($person->last_name) ) continue;

                    $contact_person = new ContactPerson;
                    $contact_person->tenant_id = Auth::user()->tenant->id;
                    $contact_person->contact_id = $contact_id;
                    $contact_person->salutation = $person->salutation;
                    $contact_person->first_name = $person->first_name;
                    $contact_person->last_name = $person->last_name;
                    $contact_person->email = $person->email;
                    $contact_person->work_phone = $person->work_phone;
                    $contact_person->mobile = $person->mobile;
                    $contact_person->save();
                }
            }

            DB::commit();

            return [
				'status' => 'success',
				'data' => [
					'id' => $contact_id,
				],
				'messages' => ['Contact created.']
			];

        } catch (\Exception $e) {
            DB::rollBack();

            $messages = [];
            $messages[] = 'System error: Please try again.';

            if (App::environment('dev')) {
                $messages[] = 'Error: Failed to save contact to database.';
                $messages[] = "\n".'File: '. $e->getFile();
                $messages[] = "\n".'Line: '. $e->getLine();
                $messages[] = "\n".'Message: ' . $e->getMessage();
            }

            return [
				'status' => 'error',
				'data' => [],
				'messages' => $messages,
			];
        }

    }

    public function show($id)
    {
    	$contact = $this->contact($id);
        return [
			'status' => 'success',
			'data' => $contact,
			'messages' => []
		];
    }

    public function edit($id)
    {
        return [
			'status' => 'error',
			'data' => [],
			'messages' => ['Unknown request (edit)'],
		];
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
        	'external_key' => ['max:100', Rule::unique('rg_contact_contacts')->ignore($id)],
            'types' => ['required', 'array'],
            'first_name' => ['required', 'string', 'max:255'],
            'other_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string'],
            'currencies' => 'array',
            'country' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $response = ['status' => 'error', 'data' => [], 'messages' => []];
            foreach ($validator->errors()->all() as $field => $message) {
                $response['messages'][] = $message;
            }
            return $response;
        }

        $contact = $this->contact($id);

        DB::beginTransaction();

        try {

            if ($request->external_key) {
				$contact->external_key  = $request->external_key;
			}

            if ($request->file('image')) {
                $contact->image = Storage::disk('public_storage')->putFile('/', $request->file('image'));;
            }

            $contact->types = json_encode($request->types);
            $contact->salutation = ($request->salutation == 'none') ? '' : $request->salutation;
            $contact->first_name = $request->first_name;
            $contact->other_name = $request->other_name;
            $contact->display_name = $request->display_name;
            $contact->currency = $request->currency;
            $contact->currencies = json_encode($request->currencies);
            $contact->country = $request->country;
            $contact->payment_terms = $request->payment_terms;

            $contact->facebook_link = $request->facebook_link;
            $contact->twitter_link = $request->twitter_link;
            $contact->remarks = $request->remarks;

            $contact->contact_salutation = ($request->contact_salutation ? $request->contact_salutation : $request->salutation);
            $contact->contact_first_name = ($request->contact_first_name ? $request->contact_first_name : $request->first_name);
            $contact->contact_last_name = ($request->contact_last_name ? $request->contact_last_name : $request->last_name);
            $contact->contact_email = ($request->contact_email ? $request->contact_email : $request->email);
            $contact->contact_work_phone = $request->contact_work_phone;

            $contact->billing_address_attention = $request->billing_address_attention;
            $contact->billing_address_street1 = $request->billing_address_street1;
            $contact->billing_address_street2 = $request->billing_address_street2;
            $contact->billing_address_city = $request->billing_address_city;
            $contact->billing_address_state = $request->billing_address_state;
            $contact->billing_address_zip_code = $request->billing_address_zip_code;
            $contact->billing_address_country = $request->billing_address_country;
            $contact->billing_address_fax = $request->billing_address_fax;

            $contact->shipping_address_attention = $request->shipping_address_attention;
            $contact->shipping_address_street1 = $request->shipping_address_street1;
            $contact->shipping_address_street2 = $request->shipping_address_street2;
            $contact->shipping_address_city = $request->shipping_address_city;
            $contact->shipping_address_state = $request->shipping_address_state;
            $contact->shipping_address_zip_code = $request->shipping_address_zip_code;
            $contact->shipping_address_country = $request->shipping_address_country;
            $contact->shipping_address_fax = $request->shipping_address_fax;

            $contact->save();

            $contact->contact_persons()->delete(); //delete all contact persons data

            if ($request->contact_person) {
                foreach ($request->contact_person as $index => $person) {

                    if ($index == '_index_') continue;

                    //print_r($person); exit;

                    if (empty($person->first_name) || empty($person->last_name) ) continue;

                    $contact_person = new ContactPerson;
                    $contact_person->tenant_id = Auth::user()->tenant->id;
                    $contact_person->contact_id = $id;
                    $contact_person->salutation = $person->salutation;
                    $contact_person->first_name = $person->first_name;
                    $contact_person->last_name = $person->last_name;
                    $contact_person->email = $person->email;
                    $contact_person->work_phone = $person->work_phone;
                    $contact_person->mobile = $person->mobile;
                    $contact_person->save();
                }
            }

            DB::commit();

            return [
				'status' => 'success',
				'data' => [
					'id' => $id,
				],
				'messages' => ['Contact Updated.']
			];

        } catch (\Exception $e) {

            DB::rollBack();

            $messages = [];
            $messages[] = 'System error: Please try again.';

            if (App::environment('dev')) {
                $messages[] = 'Error: Failed to save contact to database.';
                $messages[] = "\n".'File: '. $e->getFile();
                $messages[] = "\n".'Line: '. $e->getLine();
                $messages[] = "\n".'Message: ' . $e->getMessage();
            }

            return [
				'status' => 'error',
				'data' => [],
				'messages' => $messages,
			];
        }
    }

    public function destroy($id)
	{
		$contact = $this->contact($id);
		$contact->delete();

		return [
			'status' => 'success',
			'data' => [],
			'messages' => ['Contact successfully deleted'],
		];
	}

    public function remarks($id)
    {
    	$contact = $this->contact($id);

    	return [
			'status' => 'success',
			'data' => $contact->remarks,
			'messages' => []
		];
    }

    public function comments($id)
    {
    	$contact = $this->contact($id);

    	return [
			'status' => 'success',
			'data' => $contact->comments,
			'messages' => []
		];
    }

    public function deactivate(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            $response = ['status' => 'error', 'data' => [], 'messages' => []];
            foreach ($validator->errors()->all() as $field => $message) {
                $response['messages'][] = $message;
            }
            return $response;
        }

    	$affectedRows = Contact::whereIn('id', $request->ids)->update(['status' => 'inactive']);

    	return [
			'status' => 'success',
			'data' => [],
			'messages' => [$affectedRows . ' Contact(s) deactivated.']
		];
    }

    public function activate(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            $response = ['status' => 'error', 'data' => [], 'messages' => []];
            foreach ($validator->errors()->all() as $field => $message) {
                $response['messages'][] = $message;
            }
            return $response;
        }

    	$affectedRows = Contact::whereIn('id', $request->ids)->update(['status' => 'active']);

		return [
			'status' => 'success',
			'data' => [],
			'messages' => [$affectedRows . ' Contact(s) activated.']
		];
    }
}
