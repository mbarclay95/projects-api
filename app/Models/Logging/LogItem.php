<?php

namespace App\Models\Logging;

use App\Repositories\Logging\LogItemsRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;
use Mbarclay36\LaravelCrud\Traits\HasRepository;

/**
 * Class LogItem
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property array payload
 *
 * @property integer log_event_id
 * @property LogEvent logEvent
 */
class LogItem extends ApiModel
{
    use HasFactory, HasRepository;

    protected static string $repository = LogItemsRepository::class;
    protected static array $apiModelAttributes = ['id', 'payload'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [];

    protected $casts = [
        'payload' => 'array'
    ];

    public function logEvent(): BelongsTo
    {
        return $this->belongsTo(LogEvent::class);
    }
}
