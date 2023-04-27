<?php

namespace App\Http\Controllers\FileExplorer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DirectoryItemController extends Controller
{
    protected static array $indexRules = [
        'path' => 'nullable|string',
    ];
    protected static array $storeRules = [
        'newName' => 'required|string',
        'workingDirectory' => 'required|string',
    ];
    protected static array $updateRules = [
        'id' => 'required|string',
        'type' => 'required|string',
        'newName' => 'required|string',
        'workingDirectory' => 'required|string',
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
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate(static::$updateRules);
        $disk = Storage::build([
            'driver' => 'local',
            'root' => '/mnt/media'
        ]);
        $disk->move($validated['workingDirectory'] . '/' . $validated['id'], $validated['workingDirectory'] . '/' . $validated['newName']);

        return new JsonResponse([
            'id' => $validated['newName'],
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
