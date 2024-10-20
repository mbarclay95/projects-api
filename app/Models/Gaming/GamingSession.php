<?php

namespace App\Models\Gaming;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property Carbon started_at
 * @property Carbon ended_at
 * @property string turn_order_type
 * @property integer current_turn
 * @property boolean allow_turn_passing
 * @property boolean skip_after_passing
 * @property boolean pause_at_beginning_of_round
 * @property boolean is_paused
 * @property integer turn_limit_seconds
 *
 * @property Collection|GamingSessionDevice[] gamingSessionDevices
 */
class GamingSession extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'created_at', 'name', 'started_at', 'ended_at', 'turn_order_type',
        'current_turn', 'allow_turn_passing', 'skip_after_passing', 'pause_at_beginning_of_round', 'is_paused',
        'turn_limit_seconds'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'gamingSessionDevices' => GamingSessionDevice::class
    ];

    public function gamingSessionDevices(): HasMany
    {
        return $this->hasMany(GamingSessionDevice::class);
    }
}
