<?php

namespace Rutatiina\Contact\Classes;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Contact\Models\ContactPerson;

class Store
{
    public $errors = [];

    public function __construct()
    {}

    public function run($request)
    {
        $validator = Validator::make($request->all(), [
            'types' => ['required', 'array'],
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string'],
            'currencies' => 'array',
            'taxes' => 'array',
            'country' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $this->errors = $validator->errors()->all();
            return false;
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
                $displayName = trim($request->name); //trim($request->salutation.' '.$request->first_name.' '.$request->other_name);
            }

            $contact = new Contact;
            $contact->tenant_id = Auth::user()->tenant->id;
            $contact->types = $request->types;
            $contact->status = 'active';
            $contact->image = $image;
            $contact->salutation = ($request->salutation == 'none') ? null : $request->salutation;
            $contact->name = $request->name;
            $contact->display_name = $displayName;
            $contact->currency = $request->currency;
            $contact->currencies = $request->currencies;
            $contact->taxes = $request->taxes;
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
            $contact->contact_mobile = $request->contact_mobile;

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


            if ($request->contact_person)
            {
                foreach ($request->contact_person as $index => $person)
                {
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

            return true;

        } catch (\Exception $e) {

            DB::rollBack();

            $this->errors[] = 'System error: Please try again.';

            Log::critical('Error: Failed to save contact to database.');
            Log::critical($e);

            if (App::environment('local')) {
                $this->errors[] = 'Error: Failed to save contact to database.';
                $this->errors[] = 'File: '. $e->getFile();
                $this->errors[] = 'Line: '. $e->getLine();
                $this->errors[] = 'Message: ' . $e->getMessage();
            }

            return false;
        }

    }

}
