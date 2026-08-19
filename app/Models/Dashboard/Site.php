<?php

namespace App\Models\Dashboard;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Site
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int sort
 * @property string name
 * @property string description
 * @property bool show
 * @property string url
 * @property int folder_id
 * @property Folder folder
 * @property int site_image_id
 * @property SiteImage siteImage
 * @property int user_id
 * @property User user
 */
class Site extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'sort', 'show', 'description', 'url', 'folder_id'];

    protected static array $apiModelEntities = [
        'siteImage' => SiteImage::class,
    ];

    protected static array $apiModelArrayEntities = [];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function siteImage(): BelongsTo
    {
        return $this->belongsTo(SiteImage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
