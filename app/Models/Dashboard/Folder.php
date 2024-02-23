<?php

namespace App\Models\Dashboard;

use App\Models\Users\User;
use App\Traits\HasApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Folder
 * @package App\Models
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property integer sort
 * @property string name
 * @property boolean show
 *
 * @property integer user_id
 * @property User user

 * @property Collection|Site[] sites
 */
class Folder extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'sort', 'show'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'sites' => Site::class,
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recalculateSitesSorting(): void
    {
        $sort = 1;
        /** @var Site $site */
        foreach ($this->sites->sortBy('sort') as $site) {
            if ($site->show) {
                if ($site->sort !== $sort) {
                    $site->sort = $sort;
                    $site->save();
                }
                $sort++;
            } elseif (!$site->show && $site->sort !== null) {
                $site->sort = null;
                $site->save();
            }
        }
    }
}
