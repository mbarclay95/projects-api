<?php

namespace App\Http\Controllers\FileExplorer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DirectoryItemController extends Controller
{
    protected static array $indexRules = [
        'path' => 'nullable|string',
    ];
    protected static array $storeRules = [
        'newName' => 'required|string',
        'workingDirectory' => 'required|string',
        'type' => 'required|string'
    ];
    protected static array $updateRules = [
        'id' => 'required|string',
        'type' => 'required|string',
        'newPath' => 'required|string',
        'workingDirectory' => 'required|string',
        'mode' => 'required|string' // mv or cp
    ];
    protected static array $destroyRules = [
        'id' => 'required|string',
        'type' => 'required|string',
        'workingDirectory' => 'required|string',
    ];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate(static::$indexRules);
        $disk = Storage::build([
            'driver' => 'local',
            'root' => '/mnt/media'
        ]);
        $directories = $disk->directories($validated['path'] ?? '');
        $files = $disk->files($validated['path'] ?? '');
        sort($directories, SORT_NATURAL);
        sort($files, SORT_NATURAL);
        $items = [];
        foreach ($directories as $directory) {
            if (isset($validated['path'])) {
                $directory = str_replace($validated['path'] . '/', '', $directory);
            }
            $items[] = [
                'id' => $directory,
                'type' => 'dir'
            ];
        }
        foreach ($files as $file) {
            if (isset($validated['path'])) {
                $file = str_replace($validated['path'] . '/', '', $file);
            }
            $items[] = [
                'id' => $file,
                'type' => 'file'
            ];
        }

        return new JsonResponse($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(static::$storeRules);
        $disk = Storage::build([
            'driver' => 'local',
            'root' => '/mnt/media'
        ]);
        $disk->makeDirectory($validated['workingDirectory'] . '/' . $validated['newName']);

        return new JsonResponse([
            'id' => $validated['newName'],
            'type' => 'dir'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate(static::$updateRules);
        $disk = Storage::build([
            'driver' => 'local',
            'root' => '/mnt/media'
        ]);
        if ($validated['mode'] == 'mv') {
            $disk->move($validated['workingDirectory'] . '/' . $validated['id'], $validated['newPath']);
        } elseif ($validated['mode'] == 'cp') {
            $disk->copy($validated['workingDirectory'] . '/' . $validated['id'], $validated['newPath']);
        } else {
            throw ValidationException::withMessages(['mode must be mv or cp']);
        }
        $split = explode('/', $validated['newPath']);

        return new JsonResponse([
            'id' => $split[count($split) - 1],
            'type' => $validated['type']
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate(static::$destroyRules);
        $disk = Storage::build([
            'driver' => 'local',
            'root' => '/mnt/media'
        ]);
        if ($validated['type'] === 'dir') {
            $disk->deleteDirectory($validated['workingDirectory'] . '/' . $validated['id']);
        } else {
            $disk->delete($validated['workingDirectory'] . '/' . $validated['id']);
        }

        return new JsonResponse([
            'success' => true
        ]);
    }
}
