<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Site;
use App\Models\Tasks\Family;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Mbarclay36\LaravelCrud\DefaultRepository;

class SitesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function createEntity($request, User $user): Model|array
    {
        $maxSort = (Site::query()
                        ->where('folder_id', '=', $request['folderId'])
                        ->max('sort')
        ) ?? 0;

        $site = new Site([
            'sort' => $maxSort + 1,
            'name' => $request['name'],
            'description' => $request['description'],
            'show' => true,
            'url' => $request['url']
        ]);
        if ($request['siteImage']) {
            $site->siteImage()->associate($request['siteImage']['id']);
        }
        $site->folder()->associate($request['folderId']);
        $site->user()->associate($user);
        $site->save();

        return $site;
    }

    /**
     * @param Site $model
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, User $user): Model|array
    {
        $show = $request['show'];
        if ($model->show != $show) {
            if ($show) {
                $model->sort = ((Site::query()
                                     ->where('folder_id', '=', $request['folderId'])
                                     ->max('sort')
                    ) ?? 0) + 1;
            } else {
                $model->sort = null;
                $model->show = $request['show'];
                $model->save();
                $model->folder->recalculateSitesSorting();
            }
        }
        $model->name = $request['name'];
        $model->show = $request['show'];
        $model->description = $request['description'];
        $model->url = $request['url'];
        $folderId = $request['folderId'];
        if ($folderId !== $model->folder_id) {
            $oldFolder = $model->folder;
            $model->folder()->associate($request['folderId']);
            $model->sort = ((Site::query()
                                 ->where('folder_id', '=', $folderId)
                                 ->max('sort')
                ) ?? 0) + 1;
            $model->save();
            $oldFolder->recalculateSitesSorting();
        }
        if ($request['siteImage'] && $request['siteImage']['id']) {
            $model->siteImage()->associate($request['siteImage']['id']);
        }
        $model->save();

        return $model;
    }

    public static function updateSitesSorts($request, User $user): bool
    {
        $maxSort = (Site::query()
                        ->where('folder_id', '=', $request['folderId'])
                        ->max('sort')
        ) ?? 0;
        foreach ($request['data'] as $movedSort) {
            if ($movedSort['sort'] > $maxSort) {
                $movedSort['sort'] = $maxSort;
            }
            Site::query()
                ->where('folder_id', '=', $request['folderId'])
                ->where('id', '=', $movedSort['id'])
                ->update(['sort' => $movedSort['sort']]);
        }

        return true;
    }

    /**
     * @param Site $model
     * @param User $user
     * @return bool
     */
    public function destroyEntity(Model $model, User $user): bool
    {
        $folder = $model->folder;
        $model->delete();
        $folder->recalculateSitesSorting();

        return true;
    }
}
