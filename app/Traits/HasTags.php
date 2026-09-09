<?php

namespace App\Traits;

use App\Models\Tags\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasTags
{
    public function updateTags(array $newTags): static
    {
        $currentTags = $this->tags;
        $tagsToInsert = [];
        /** @var string $newTag */
        foreach ($newTags as $newTag) {
            if ($currentTags->doesntContain(function (Tag $currentTag) use ($newTag) {
                return $newTag == $currentTag->tag;
            })) {
                $tagsToInsert[] = new Tag(['tag' => $newTag]);
            }
        }
        $this->tags()->saveMany($tagsToInsert);

        foreach ($currentTags as $currentTag) {
            if (Collection::make($newTags)->doesntContain(function (string $newTag) use ($currentTag) {
                return $currentTag->tag == $newTag;
            })) {
                $this->tags()->detach($currentTag);
            }
        }

        $this->load('tags');

        return $this;
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
