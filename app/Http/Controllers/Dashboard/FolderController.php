<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Folder::class, 'folder');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        /** @var Folder[] $folders */
        $folders = Folder::query()
                         ->where('user_id', '=', $userId)
                         ->get();

        return new JsonResponse(Folder::toApiModels($folders));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $maxSort = (Folder::query()->max('sort')) ?? 0;

        $folder = new Folder([
            'name' => $request->post('name'),
            'sort' => $maxSort + 1,
            'show' => true
        ]);
        $folder->user()->associate($userId);
        $folder->save();

        return new JsonResponse(Folder::toApiModel($folder));
    }

    /**
     * Display the specified resource.
     *
     * @param Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function show(Folder $folder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Folder $folder
     * @return JsonResponse
     */
    public function update(Request $request, Folder $folder): JsonResponse
    {
        $folder->name = $request->post('name');
        $folder->show = $request->post('show');
        $folder->save();

        return new JsonResponse(Folder::toApiModel($folder));
    }

    public function updateFolderSorts(Request $request): JsonResponse
    {
        // ADD AUTH

        $data = $request->post('data');

        foreach ($data as $movedSort) {
            Folder::query()
                  ->where('id', '=', $movedSort['id'])
                  ->update(['sort' => $movedSort['sort']]);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Folder $folder
     * @return JsonResponse
     */
    public function destroy(Folder $folder): JsonResponse
    {
        $userId = Auth::id();
        $updateSortFolders = Folder::query()
                                   ->where('user_id', '=', $userId)
                                   ->where('sort', '>', $folder->sort)
                                   ->get();

        /** @var Folder $updateSortFolder */
        foreach ($updateSortFolders as $updateSortFolder) {
            $updateSortFolder->sort -= 1;
            $updateSortFolder->save();
        }
        $folder->delete();

        return new JsonResponse(['success' => true]);
    }
}
