<?php

namespace Rutatiina\Contact\Models;

use Rutatiina\Tenant\Scopes\TenantIdScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rutatiina\FinancialAccounting\Traits\Forex;
use Rutatiina\Tenant\Models\Tenant;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Rutatiina\FinancialAccounting\Classes\AccountClass;
use Illuminate\Support\Facades\Schema;

class Contact extends Model
{
	use SoftDeletes;
    use LogsActivity;
    //use Forex;

    protected static $logName = 'Contact';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_contacts';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $appends = [
        'receviables',
        'payables',
        'currency_and_exchange_rate',
        'currencies_and_exchange_rates'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'types' => 'array',
        'currencies' => 'array',
        'taxes' => 'array',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TenantIdScope);
    }

    public function rgGetAttributes()
    {
        $attributes = [];
        $describeTable =  \DB::connection('tenant')->select('describe ' . $this->getTable());

        foreach ($describeTable  as $row) {

            if (in_array($row->Field, ['id', 'created_at', 'updated_at', 'deleted_at', 'tenant_id', 'user_id'])) continue;

            if (in_array($row->Field, ['currencies', 'taxes'])) {
                $attributes[$row->Field] = [];
                continue;
            }

            if ($row->Default == '[]') {
                $attributes[$row->Field] = [];
            } else {
                $attributes[$row->Field] = $row->Default;
            }
        }

        //add the relationships
        $attributes['comments'] = [];
        $attributes['contact_persons'] = [];
        $attributes['address_book'] = [];

        return $attributes;
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->other_name}";
    }

    /*
    public function getExchangeRatesAttribute()
    {
        if (!Auth::check()) {
            return [];
        }

        $exchangeRates = [];
        $currencies = array_unshift($this->currencies, $this->currency);
        foreach ($currencies as $currency) {
            $exchangeRates[$currency] = RgForex::exchangeRate(Auth::user()->tenant->base_currency, $currency);
        }
        return $exchangeRates;
    }
    */

    public function getReceviablesAttribute()
    {
        //1 - Receivables
        $balance = AccountClass::date(null)->accountCode(config('financial-accounting.accounts_receivable_code'))->contactId($this->id)->currency($this->currency)->balanceByContact(true);
        if ($balance) {
            return ($balance->debit - $balance->credit);
        } else {
            return 0;
        }
    }

    public function getPayablesAttribute()
    {
        //4 - Payables
        $balance = AccountClass::date(null)->accountCode(config('financial-accounting.accounts_payables_code'))->contactId($this->id)->currency($this->currency)->balanceByContact(true);

        if ($balance) {
            return ($balance->credit - $balance->debit);
        } else {
            return 0;
        }
    }

    public function getCategoriesAttribute($value)
    {
        $_array_ = json_decode($value);
        if (is_array($_array_)) {
            return $_array_;
        } else {
            return [];
        }
    }

    public function getCurrencyAndExchangeRateAttribute()
    {
        $tenant = Tenant::find($this->tenant_id); //Auth::user()->tenant;
        $quote_currency = ($this->currency) ? $this->currency : $tenant->base_currency;
        $exchangeRate = exchangeRate($tenant->base_currency, $quote_currency);

        return [
            'code' => $this->currency,
            'exchangeRate' => $exchangeRate
        ];
    }

    public function getCurrenciesAndExchangeRatesAttribute()
    {
        $tenant = Tenant::find($this->tenant_id);
        $contactCurrencies = (is_array($this->currencies)) ? $this->currencies : [];
        $currencies = [];
        $contactExchangeRate = exchangeRates($contactCurrencies, $tenant->base_currency);

        foreach($contactExchangeRate as $currency => $exchangeRate) {

            $c = [
                'code' => $currency,
                'exchangeRate' => $exchangeRate
            ];

            $currencies[] = $c;
        }

        return $currencies;
    }

    public function user()
    {
        return $this->hasOne('Rutatiina\Contact\Models\User', 'contact_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany('Rutatiina\Contact\Models\Comment', 'contact_id');
    }

    public function contact_persons()
    {
        return $this->hasMany('Rutatiina\Contact\Models\ContactPerson', 'contact_id');
    }

    public function address_book()
    {
        return $this->hasMany('Rutatiina\Contact\Models\AddressBook', 'contact_id');
    }

    public function getSearchableColumns()
    {
        return Schema::connection('tenant')->getColumnListing($this->table);
    }
}
