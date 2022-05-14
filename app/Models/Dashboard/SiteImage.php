<?php

namespace App\Models\Dashboard;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class SiteImage
 * @package App\Models
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string original_file_name
 * @property string s3_path
 *
 * @property integer user_id
 * @property User user
 *
 * @property Site site
 */
class SiteImage extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    public function site(): HasOne
    {
        return $this->hasOne(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
