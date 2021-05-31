<?php

namespace Rutatiina\Contact\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Comment extends Model
{
    use LogsActivity;

    protected static $logName = 'Comment';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_contact_comments';

    protected $primaryKey = 'id';

    /**
     * Get the Contact.
     */
    public function contact()
    {
        return $this->belongsTo('Rutatiina\Contact\Models\Contact', 'id');
    }
}
