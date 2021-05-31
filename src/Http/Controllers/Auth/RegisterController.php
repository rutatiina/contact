<?php

namespace Rutatiina\Contact\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rutatiina\Contact\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Rutatiina\Contact\Models\Contact;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:contact',['only' => 'index','edit']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('contact.profile');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('contact::auth.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required',
            'other_name'    => 'required',
			'email' => ['required', 'string', 'email', 'max:255', 'unique:rg_contact_users'],
            'password' => ['required', 'string', 'min:3', 'confirmed'],
        ]);

        if ($validator->fails()) {
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }

        DB::transaction(function () use ($request) {

            $contact                    = new Contact;
            $contact->tenant_id         = 1;
            $contact->status            = 'active';
            $contact->first_name        = $request->first_name;
            $contact->other_name        = $request->other_name;
            $contact->display_name      = $request->first_name .' '. $request->other_name;
            $contact->contact_first_name = $request->first_name;
            $contact->contact_last_name = $request->other_name;
            $contact->contact_email     = $request->email;
            $contact->save();

            // store in the database
            $user               = new User;
            $user->tenant_id    = 1;
            $user->contact_id   = $contact->id;
            $user->name         = $request->first_name. ' '.$request->other_name;
            $user->email        = $request->email;
            $user->password     = bcrypt($request->password);
            $user->save();

        });

        return redirect()->route('contact.auth.login');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
