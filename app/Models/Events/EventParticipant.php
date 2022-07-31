<?php

namespace App\Models\Events;

use App\Models\BaseApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Event
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property boolean is_going
 * @property boolean notification_email
 *
 * @property integer event_id
 * @property Event event
 */
class EventParticipant extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'is_going'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
