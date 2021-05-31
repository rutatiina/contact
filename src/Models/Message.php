<?php

namespace Rutatiina\Contact\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Message extends Model
{
    use LogsActivity;

    protected static $logName = 'Message';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;


    //use SoftDeletes;

	protected $connection = 'tenant';

    protected $table = 'rg_messaging_messages';

    protected $primaryKey = 'id';

    //protected $dates = ['deleted_at'];

    public function sender()
    {
        return $this->belongsTo('Rutatiina\Contact\Models\Contact', 'id', 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo('Rutatiina\Contact\Models\Contact', 'id', 'receiver_id');
    }

}
