<?php

namespace App\Models\Gaming;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string code
 * @property string session_type
 *
 * @property Collection|GamingSessionDevice[] gamingSessionDevices
 */
class GamingSession extends Model
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'code', 'session_type'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'gamingSessionDevices' => GamingSessionDevice::class
    ];

    public function gamingSessionDevice(): HasMany
    {
        return $this->hasMany(GamingSessionDevice::class);
    }
}
