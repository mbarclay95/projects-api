<?php

namespace App\Models\Logging;

use App\Enums\LogSourceEnum;
use App\Repositories\Logging\LogEventsRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;
use Mbarclay36\LaravelCrud\Traits\HasRepository;

/**
 * Class LogEvent
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property LogSourceEnum source
 *
 * @property Collection|LogItem[] logItems
 */
class LogEvent extends ApiModel
{
    use HasFactory, HasRepository;

    protected static string $repository = LogEventsRepository::class;
    protected static array $apiModelAttributes = ['id', 'source'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [
        'logItems' => LogItem::class,
    ];

    public function logItems(): HasMany
    {
        return $this->hasMany(LogItem::class);
    }
}
