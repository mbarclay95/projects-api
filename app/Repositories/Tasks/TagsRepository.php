<?php

namespace App\Repositories\Tasks;

use App\Models\Families\Family;
use App\Models\Tasks\Tag;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TagsRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return Tag::query()
            ->whereHas('tasks', function ($where) use ($user) {
                $where->where(function ($innerWhere) use ($user) {
                    $innerWhere
                        ->orWhere(function ($userWhere) use ($user) {
                            $userWhere->where('owner_type', '=', (new User)->getMorphClass())
                                ->where('owner_id', '=', $user->id);
                        })
                        ->when($user->family, function ($familyCondition) use ($user) {
                            $familyCondition->orWhere(function ($familyWhere) use ($user) {
                                $familyWhere->where('owner_type', '=', (new Family)->getMorphClass())
                                    ->where('owner_id', '=', $user->family->id);
                            });
                        });
                });
            })
            ->select('tag')
            ->distinct()
            ->get();
    }
}
