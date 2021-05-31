<?php

namespace Rutatiina\Contact\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Rutatiina\Contact\Models\AddressBook;
use Rutatiina\Contact\Traits\ContactTrait;

class AddressBookController extends Controller
{

    use ContactTrait;

    public function __construct()
    {
        $this->middleware('auth:contact');
    }

    public function buyers() {

        static::contactByUserId(); //set static::$contact in ContactTrait

        $buyers = AddressBook::where('contact_id', static::$contact->id)->where('is_buyer', 1)->get();
        return view('contact::buyers')->with([
            'buyers' => $buyers,
            'buyer' => $buyers->first(),
        ]);
    }

    public function sellers() {

        static::contactByUserId(); //set static::$contact in ContactTrait

        $sellers = AddressBook::where('contact_id', static::$contact->id)->where('is_seller', 1)->get();
        return view('contact::sellers')->with([
            'sellers' => $sellers,
            'seller' => $sellers->first(),
        ]);
    }
}
