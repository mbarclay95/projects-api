<?php

namespace App\Models\BoardGames;

use App\Enums\BoardGamePlayStatusEnum;
use App\Enums\BoardGameTypeEnum;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class BoardGamePlay
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int user_id
 * @property BoardGameTypeEnum game_type
 * @property string|null name
 * @property BoardGamePlayStatusEnum status
 * @property array|null state
 * @property User user
 */
class BoardGamePlay extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = [
        'id', 'user_id', 'game_type', 'name', 'status', 'state', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'game_type' => BoardGameTypeEnum::class,
        'status' => BoardGamePlayStatusEnum::class,
        'state' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function toApiModels(array|Collection $models, array $hideItem = []): array
    {
        return parent::toApiModels($models, [...$hideItem, 'state']);
    }
}
