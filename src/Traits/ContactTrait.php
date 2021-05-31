<?php

namespace Rutatiina\Contact\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\FinancialAccounting\Classes\Forex;

trait ContactTrait
{
    protected static $only_instance;
    public static $contact;

    public function getSelf() {

        //parent::getSelf();

        if (method_exists(__CLASS__,'getSelf')) {
            parent::getSelf();
        } else {
            if (static::$only_instance === null)
            {
                static::$only_instance = new self();
            }
            return static::$only_instance;
        }
    }

    public static function contactByUserId()
    {
        static::$contact = Contact::find(Auth::user()->contact_id);
        return new static;
    }

    public static function contactById($id)
    {
        static::$contact = Contact::find($id);
        return new static;
    }

    public static function contactsByTypes($types, $select2 = false)
    {
        $contacts = [];

        foreach($types as $type) {
            $contacts[ucfirst($type)] = Contact::where('tenant_id', Auth::user()->tenant->id)
                ->where('types', 'like' , '%'.$type.'%')
				->orderBy('display_name', 'asc')
				->orderBy('name', 'asc')
                ->get();
        }

        if ($select2 == true) {
            $select2Data = [];
            foreach($contacts as $group => $_contacts) {
                $optionGroup['text'] = ucfirst($group);
                foreach ($_contacts as $contact) {

                    $contactCurrency = [];
                    $currencies = [];
                    $contactExchangeRate = Forex::contactExchangeRate($contact);

                    foreach($contactExchangeRate as $currency => $exchangeRate) {

                        $c = [
                            'value' => $currency,
                            'text' => $currency,
                            'exchangeRate' => $exchangeRate
                        ];

                        if ($contact->currency == $currency) {
                            $contactCurrency = $c;
                        }

                        $currencies[] = $c;
                    }

                    $optionGroup['children'][] = [
                        'id' => $contact->id,
                        'text' => $contact->display_name,
                        'currencies' => $currencies,
                        'currency' => $contactCurrency,
                    ];
                }

                //$select2Data[] = $optionGroup;
                $select2Data = $optionGroup['children'];
            }

            return $select2Data;
        }

        return $contacts;
    }
}
