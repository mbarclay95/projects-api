<?php

namespace App\Repositories\Tags;

use App\Enums\TagScopeEnum;
use App\Models\Tags\Tag;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TagsRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return match (TagScopeEnum::from($request['scope'])) {
            TagScopeEnum::TASKS => $this->taskTags($user),
        };
    }

    private function taskTags(Authenticatable $user): Collection
    {
        return Tag::query()
            ->whereHas('tasks', function ($where) use ($user) {
                $where->where(function ($innerWhere) use ($user) {
                    $innerWhere
                        ->orWhere(function ($userWhere) use ($user) {
                            $userWhere->where('owner_type', '=', (new User)->getMorphClass())
                                ->where('owner_id', '=', $user->id);
                        })
                        ->when($user->taskGroup, function ($familyCondition) use ($user) {
                            $familyCondition->orWhere(function ($familyWhere) use ($user) {
                                $familyWhere->where('owner_type', '=', (new UserGroup)->getMorphClass())
                                    ->where('owner_id', '=', $user->taskGroup->id);
                            });
                        });
                });
            })
            ->select('tag')
            ->distinct()
            ->get();
    }
}
