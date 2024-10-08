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
 * @property string code
 * @property string session_type
 * @property boolean is_active
 *
 * @property Collection|GamingSessionDevice[] gamingSessionDevices
 */
class GamingSession extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'code', 'session_type', 'is_active'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'gamingSessionDevices' => GamingSessionDevice::class
    ];

    public function gamingSessionDevices(): HasMany
    {
        return $this->hasMany(GamingSessionDevice::class);
    }
}
