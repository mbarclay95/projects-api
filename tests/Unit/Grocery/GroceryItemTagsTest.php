<?php

namespace Tests\Unit\Grocery;

use App\Models\Grocery\GroceryItem;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GroceryItemTagsTest extends TestCase
{
    public function test_updating_tags_writes_a_taggables_row_per_tag_aliased_as_grocery_item(): void
    {
        $item = GroceryItem::factory()->create();

        $item->updateTags(['costco', 'produce']);

        self::assertEquals(
            ['grocery-item', 'grocery-item'],
            DB::table('taggables')->where('taggable_id', $item->id)->pluck('taggable_type')->all()
        );
    }

    public function test_updating_tags_again_detaches_the_removed_tag(): void
    {
        $item = GroceryItem::factory()->create();
        $item->updateTags(['costco', 'produce']);

        $item->updateTags(['costco']);

        self::assertEquals(['costco'], $item->fresh()->tags->pluck('tag')->all());
    }
}
