<?php

namespace App\Models\Gaming;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property integer current_turn_order
 * @property integer next_turn_order
 * @property string turn_time_display_mode
 * @property boolean skip
 * @property boolean has_passed
 *
 * @property integer gaming_device_id
 * @property GamingDevice gamingDevice
 *
 * @property integer gaming_session_id
 * @property GamingSession gamingSession
 */
class GamingSessionDevice extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'current_turn_order', 'next_turn_order',
        'turn_time_display_mode', 'skip', 'has_passed'];

    protected static array $apiModelEntities = [
        'gamingDevice' => GamingDevice::class,
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    protected static array $apiModelArrayEntities = [];

    public function gamingDevice(): BelongsTo
    {
        return $this->belongsTo(GamingDevice::class);
    }

    public function gamingSession(): BelongsTo
    {
        return $this->belongsTo(GamingSession::class);
    }
}
