<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\SiteImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiteImageController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SiteImage::class, 'site-image', ['except' => ['show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
        $file = $request->file('file');
        $path = Storage::disk('s3')->put('site-images', $file);

        $siteImage = new SiteImage([
            's3_path' => $path,
            'original_file_name' => $file->getClientOriginalName()
        ]);
        $siteImage->user()->associate($userId);
        $siteImage->save();

        return new JsonResponse(SiteImage::toApiModel($siteImage));
    }

    /**
     * Display the specified resource.
     *
     * @param SiteImage $siteImage
     * @return StreamedResponse
     */
    public function show(SiteImage $siteImage): StreamedResponse
    {
        $file = Storage::disk('s3')->get($siteImage->s3_path);
        
        if (str_contains($siteImage->s3_path, '.svg')) {
            return response()->stream(function () use ($file) {
                echo $file;
            }, 200, ['Content-Type' => 'image/svg+xml']);
        }

        return response()->stream(function () use ($file) {
            echo $file;
        }, 200, ['Content-Type' => 'image/png']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SiteImage $siteImage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SiteImage $siteImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SiteImage $siteImage
     * @return \Illuminate\Http\Response
     */
    public function destroy(SiteImage $siteImage)
    {
        //
    }
}
