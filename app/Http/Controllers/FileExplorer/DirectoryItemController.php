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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
