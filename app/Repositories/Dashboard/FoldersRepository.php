<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Folder;
use App\Models\Tasks\Family;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class FoldersRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param User $user
     * @param bool $viewOnlyForUser
     * @return Collection|Folder[]
     */
    public function getEntities($request, User $user, bool $viewOnlyForUser): Collection|array
    {
        return Folder::query()
                     ->with('sites.siteImage')
                     ->where('user_id', '=', $user->id)
                     ->get();
    }

    /**
     * @param $request
     * @param User $user
     * @return Folder|array
     */
    public function createEntity($request, User $user): Model|array
    {
        $maxSort = (Folder::query()->max('sort')) ?? 0;

        $folder = new Folder([
            'name' => $request['name'],
            'sort' => $maxSort + 1,
            'show' => true
        ]);
        $folder->user()->associate($user);
        $folder->save();

        return $folder;
    }

    /**
     * @param Folder $model
     * @param $request
     * @param User $user
     * @return Folder|array
     */
    public function updateEntity(Model $model, $request, User $user): Model|array
    {
        $model->name = $request['name'];
        $model->show = $request['show'];
        $model->save();

        return $model;
    }

    /**
     * @param Folder $model
     * @param User $user
     * @return bool
     */
    public function destroyEntity(Model $model, User $user): bool
    {
        /** @var Folder[] $updateSortFolders */
        $updateSortFolders = Folder::query()
                                   ->where('user_id', '=', $user->id)
                                   ->where('sort', '>', $model->sort)
                                   ->get();

        foreach ($updateSortFolders as $updateSortFolder) {
            $updateSortFolder->sort -= 1;
            $updateSortFolder->save();
        }
        $model->delete();

        return true;
    }
}
