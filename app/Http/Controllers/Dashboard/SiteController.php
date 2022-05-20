<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Site::class, 'site');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
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
        $maxSort = (Site::query()->where('folder_id', '=', $request->post('folderId'))->max('sort')) ?? 0;

        $site = new Site([
            'sort' => $maxSort + 1,
            'name' => $request->post('name'),
            'description' => $request->post('description'),
            'show' => true,
            'url' => $request->post('url')
        ]);
        if ($request->post('siteImage')) {
            $site->siteImage()->associate($request->post('siteImage')['id']);
        }
        $site->folder()->associate($request->post('folderId'));
        $site->user()->associate($userId);
        $site->save();

        return new JsonResponse(Site::toApiModel($site));
    }

    /**
     * Display the specified resource.
     *
     * @param Site $site
     * @return Response
     */
    public function show(Site $site)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Site $site
     * @return JsonResponse
     */
    public function update(Request $request, Site $site): JsonResponse
    {
        $site->name = $request->post('name');
        $site->show = $request->post('show');
        $site->description = $request->post('description');
        $site->url = $request->post('url');
        $site->folder()->associate($request->post('folderId'));
        if ($request->post('siteImage') && $request->post('siteImage')['id']) {
            $site->siteImage()->associate($request->post('siteImage')['id']);
        }
        $site->save();

        return new JsonResponse(Site::toApiModel($site));
    }

    public function updateSiteSorts(Request $request): JsonResponse
    {
        // ADD AUTH

        $data = $request->post('data');

        foreach ($data as $movedSort) {
            Site::query()
                ->where('id', '=', $movedSort['id'])
                ->update(['sort' => $movedSort['sort']]);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Site $site
     * @return JsonResponse
     */
    public function destroy(Site $site): JsonResponse
    {
        $updateSortSites = Site::query()
                               ->where('sort', '>', $site->sort)
                               ->get();

        /** @var Site $updateSortSite */
        foreach ($updateSortSites as $updateSortSite) {
            $updateSortSite->sort -= 1;
            $updateSortSite->save();
        }

        $site->delete();

        return new JsonResponse(['success' => true]);
    }
}
