<?php

namespace Rutatiina\Contact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Rutatiina\Contact\Models\AddressBook;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Contact\Models\Message;
use Rutatiina\Classes\SmsSmsone as ClassSmsSmsone;
use App\SmsOutbox;

class MessageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:contact');
    }

    public function messenger($contactIdEncrypted = null, Request $request) {

        $user_contact = Contact::find(Auth::user()->contact_id);

        //get the details of the last message sent or received
        if (!empty($contactIdEncrypted)) {
            $receiverId = Crypt::decryptString($contactIdEncrypted);
        } else {
            $lastMessage = Message::where('sender_id', $user_contact->id)->orWhere('receiver_id', $user_contact->id)->orderBy('created_at', 'desc')->first();

            if ($lastMessage) {
                $receiverId = ($lastMessage->sender_id == $user_contact->id) ? $lastMessage->receiver_id : $lastMessage->sender_id;
            } else {
                $receiverId = null;
            }
        }


        /*
         * check if the contact is in the address book.
         * If not, add the contact
         * */
        $addressBook = AddressBook::where('contact_id', $user_contact->id)->where('address_id', $receiverId)->first();

        if (empty($addressBook) && !empty($receiverId) && is_numeric($receiverId)) {
            $addressBook                = new AddressBook;
            $addressBook->tenant_id     = Auth::user()->tenant->id;
            $addressBook->user_id       = Auth::id();
            $addressBook->contact_id    = $user_contact->id;
            $addressBook->address_id    = $receiverId;
            $addressBook->save();

            $addressBook                = new AddressBook;
            $addressBook->tenant_id     = Auth::user()->tenant->id;
            $addressBook->user_id       = Auth::id();
            $addressBook->contact_id    = $receiverId;
            $addressBook->address_id    = $user_contact->id;
            $addressBook->save();
        }

        //get the address book of the contact
        //var_dump($user_contact->address_book->count()); exit;

        //$contacts = Contact::where('id', '!=', $user_contact->id)->get();
        foreach ($user_contact->address_book as &$address) {

            $address->last_message = Message::where(function($query) use($user_contact) {
                    $query->where('sender_id', $user_contact->id)->orWhere('receiver_id', $user_contact->id);
                })
                ->where(function($query) use ($address) {
                    $query->where('sender_id', $address->address_id)->orWhere('receiver_id', $address->address_id);
                })
                ->orderBy('id', 'desc')->first();
        }
        unset($address);

        $messages = Message::where(function($query) use($user_contact) {
                $query->where('sender_id', $user_contact->id)->orWhere('receiver_id', $user_contact->id);
            })
            ->where(function($query) use ($receiverId) {
                $query->where('sender_id', $receiverId)->orWhere('receiver_id', $receiverId);
            })
            ->orderBy('id', 'asc')->limit(50)->get();

        $contact = Contact::find($receiverId);
        //var_dump($contact->first_name); exit;
        //print_r($user_contact->address_book); exit;

        if ($request->isMethod('get')) {
            return view('contact::messaging_messenger')->with([
                'user_contact' => $user_contact,
                //'contacts' => $contacts,
                'contact' => $contact,
                'receiver_id' => $receiverId,
                'messages' => $messages,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|numeric',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }

        $message                = new Message;
        $message->tenant_id     = Auth::user()->tenant->id;
        $message->sender_id     = $user_contact->id;
        $message->receiver_id   = $request->receiver_id;
        $message->message       = $request->message;

        if ($message->save()) {
            return redirect()->back();//->with(['success' => 'Message sent']);
        } else {
            return redirect()->back()->withErrors(['message' => 'Error: Failed to send message.']);
        }

    }

    public function email($contactIdEncrypted = null, Request $request) {

        return view('contact::messaging_email');

    }

    public function sms($contactIdEncrypted = null, Request $request) {

        if ($request->isMethod('get')) {

            return view('contact::messaging_sms')->with([
                'smsOutbox' => SmsOutbox::paginate(20)
            ]);

        } elseif ($request->isMethod('post')) {

            //var_dump($request->phone_number); exit;

            $validationRules = [
                'phone_number' => 'required|digits:12',
                'message' => 'required|string|min:3',
            ];

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                $request->flash();
                return redirect()->back()->withErrors($validator);
            }

            $sendSms = ClassSmsSmsone::phoneNumber($request->phone_number)->message($request->message)->subject('DREAMASSET')->send();

            if ($sendSms->status == 'S') {
                return redirect()->back()->with(['success' => 'SMS sent.']);
            }

            return redirect()->back()->withErrors(['message'=>'An error occurred, please try again']);

        }

    }
}
