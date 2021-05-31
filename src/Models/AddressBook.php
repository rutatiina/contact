<?php

namespace Rutatiina\Contact\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class AddressBook extends Model
{
    use LogsActivity;

    protected static $logName = 'AddressBook';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;


    //use SoftDeletes;

	protected $connection = 'tenant';

    protected $table = 'rg_contact_address_book';

    protected $primaryKey = 'id';

    //protected $dates = ['deleted_at'];

    /**
     * Get the item.
     */
    public function contact()
    {
        return $this->belongsTo('Rutatiina\Contact\Models\Contact', 'address_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo('Rutatiina\Contact\Models\Contact', 'contact_id', 'id');
    }

}
