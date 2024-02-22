<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\Folder;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mbarclay36\LaravelCrud\CrudController;

class FolderController extends CrudController
{
    protected static string $modelClass = Folder::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'show' => 'required|boolean',
    ];

    public function updateFolderSorts(Request $request): JsonResponse
    {
//        /** @var User $user */
//        $user = Auth::user();
//        if ($this->cannotUpdate($user)) {
//            throw new AuthenticationException();
//        }

        $data = $request->post('data');

        foreach ($data as $movedSort) {
            Folder::query()
                  ->where('id', '=', $movedSort['id'])
                  ->update(['sort' => $movedSort['sort']]);
        }

        return new JsonResponse(['success' => true]);
    }
}
