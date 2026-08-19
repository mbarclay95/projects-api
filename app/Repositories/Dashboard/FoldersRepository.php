<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Folder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class FoldersRepository extends DefaultRepository
{
    /**
     * @return Collection|Folder[]
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return Folder::query()
            ->with('sites.siteImage')
            ->where('user_id', '=', $user->id)
            ->get();
    }

    /**
     * @return Folder|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $maxSort = (Folder::query()->max('sort')) ?? 0;

        $folder = new Folder([
            'name' => $request['name'],
            'sort' => $maxSort + 1,
            'show' => true,
        ]);
        $folder->user()->associate($user);
        $folder->save();

        return $folder;
    }

    /**
     * @param  Folder  $model
     * @return Folder|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->show = $request['show'];
        $model->save();

        return $model;
    }

    /**
     * @param  Folder  $model
     */
    public function destroyEntity(Model $model, Authenticatable $user): bool
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
