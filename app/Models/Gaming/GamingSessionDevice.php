<?php

namespace App\Models\Gaming;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string code
 * @property string session_type
 *
 * @property integer gaming_device_id
 * @property GamingDevice gamingDevice
 *
 * @property integer gaming_session_id
 * @property GamingSession gamingSession
 */
class GamingSessionDevice extends Model
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'code', 'session_type'];

    protected static array $apiModelEntities = [
        'gamingDevice' => GamingDevice::class,
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
