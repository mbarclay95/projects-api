<?php

namespace App\Models\Grocery;

use App\Enums\GroceryItemUnit;
use App\Models\Tags\Tag;
use App\Traits\HasTags;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class GroceryItem
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string|null notes
 * @property int user_group_id
 * @property GroceryItemUnit unit
 * @property float|null default_quantity
 * @property int|null grocery_category_id
 * @property GroceryCategory|null groceryCategory
 * @property string|null category
 * @property Collection|Tag[] tags
 * @property Collection|GroceryStoreItemCategory[] exceptions
 */
class GroceryItem extends ApiModel
{
    use HasFactory, HasTags;

    protected static array $apiModelAttributes = [
        'id', 'name', 'notes', 'unit', 'default_quantity', 'grocery_category_id', 'category',
    ];

    protected static array $apiModelArrayEntities = [
        'tags' => Tag::class,
    ];

    protected $casts = [
        'unit' => GroceryItemUnit::class,
        'default_quantity' => 'float',
    ];

    public function listItems(): HasMany
    {
        return $this->hasMany(GroceryListItem::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(GroceryStoreItemCategory::class);
    }

    public function groceryCategory(): BelongsTo
    {
        return $this->belongsTo(GroceryCategory::class);
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->groceryCategory?->name;
    }
}
