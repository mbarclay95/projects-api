<?php

namespace App\Models\Grocery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class GroceryCategory
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property int user_group_id
 */
class GroceryCategory extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name'];

    public function items(): HasMany
    {
        return $this->hasMany(GroceryItem::class);
    }

    public static function resolveForGroup(?string $name, int $userGroupId): ?self
    {
        $name = trim($name ?? '');

        if ($name === '') {
            return null;
        }

        $category = self::query()
            ->where('user_group_id', '=', $userGroupId)
            ->whereRaw('lower(name) = ?', [strtolower($name)])
            ->first();

        if ($category) {
            return $category;
        }

        $category = new self([
            'name' => $name,
            'user_group_id' => $userGroupId,
        ]);
        $category->save();

        return $category;
    }
}
