<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DraftImage
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string s3_path
 * @property string original_file_name
 *
 * @property integer created_by_id
 * @property User createdBy
 */
class DraftImage extends BaseApiModel
{
    use HasFactory;

    /**
     * Narrower than SiteImage, which also exposes s3_path: the client builds
     * the image URL from the id alone and never needs the storage key.
     */
    protected static array $apiModelAttributes = ['id', 'original_file_name'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * HasMany rather than HasOne: an image is uploaded before the draft that
     * will reference it exists, so a row can be attached to any number of
     * drafts, or to none at all when a create is cancelled.
     */
    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }
}
