<?php

namespace Rutatiina\Contact\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class BankAccount extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected static $logName = 'BankAccount';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_contact_bank_accounts';

    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    /**
     * Get the Contact.
     */
    public function contact()
    {
        return $this->belongsTo('Rutatiina\Contact\Models\Contact', 'id');
    }
}

