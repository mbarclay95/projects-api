<?php

namespace App\Models\Events;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Event
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string notes
 * @property Carbon event_date
 * @property integer num_of_people
 * @property string token
 *
 * @property integer user_id
 * @property User user
 *
 * @property Collection|EventParticipant[] eventParticipants
 */
class Event extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'notes', 'event_date', 'num_of_people', 'token'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [];

    protected $dateFormat = 'Y-m-d H:i:sO';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }
}
