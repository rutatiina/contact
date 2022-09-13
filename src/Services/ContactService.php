<?php

namespace Rutatiina\Contact\Services;

use Illuminate\Support\Facades\Auth;
use Rutatiina\Contact\Models\Contact;
use Illuminate\Database\Eloquent\Model;

class ContactService extends Model
{
    public static function createAttributes()
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $contact = new Contact;
        $attributes = $contact->rgGetAttributes();

        $attributes['types'][] = 'customer';
        $attributes['country'] = $tenant->country;
        $attributes['currency'] = $tenant->base_currency;
        $attributes['currencies'] = [$tenant->base_currency];
        $attributes['_method'] = 'POST';

        return $attributes;
    }

}
