<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TagsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param User $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, User $user, bool $viewOnlyForUser): Collection|array
    {
        return Tag::query()
                  ->whereHas('tasks', function ($where) use ($user) {
                      $where->where(function ($innerWhere) use ($user) {
                          $innerWhere
                              ->orWhere(function ($userWhere) use ($user) {
                                  $userWhere->where('owner_type', '=', User::class)
                                            ->where('owner_id', '=', $user->id);
                              })
                              ->when($user->family, function ($familyCondition) use ($user) {
                                  $familyCondition->orWhere(function ($familyWhere) use ($user) {
                                      $familyWhere->where('owner_type', '=', Family::class)
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
