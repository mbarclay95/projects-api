<?php

namespace App\Models\Grocery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class GroceryStore
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property int user_group_id
 * @property array category_order
 * @property Collection|GroceryStoreItemCategory[] exceptions
 */
class GroceryStore extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'category_order'];

    protected $casts = [
        'category_order' => 'array',
    ];

    public function exceptions(): HasMany
    {
        return $this->hasMany(GroceryStoreItemCategory::class);
    }
}
