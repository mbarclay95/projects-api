<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\Folder;
use App\Models\Dashboard\Site;
use App\Models\Users\User;
use App\Repositories\Dashboard\SitesRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mbarclay36\LaravelCrud\CrudController;

class SiteController extends CrudController
{
    protected static string $modelClass = Site::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'description' => 'nullable|string',
        'url' => 'required|string',
        'siteImage' => 'nullable|array',
        'folderId' => 'required|int',
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'description' => 'nullable|string',
        'show' => 'required|boolean',
        'url' => 'required|string',
        'folderId' => 'required|int',
        'siteImage' => 'nullable|array'
    ];
    protected static array $updateSortsRules = [
        'data' => 'required|array',
        'folderId' => 'required|int',
        'data.*.id' => 'required|int',
        'data.*.sort' => 'required|int',
    ];

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updateSiteSorts(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(self::$updateSortsRules);
        $folder = Folder::query()->find($validated['folderId']);
        if ($this->cannotUpdate($user, $folder)) {
            throw new AuthenticationException();
        }

        $success = SitesRepository::updateSitesSorts($validated, $user);

        return new JsonResponse(['success' => $success]);
    }
}
