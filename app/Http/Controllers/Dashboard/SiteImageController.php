<?php

namespace App\Http\Controllers\Dashboard;

use Mbarclay36\LaravelCrud\CrudController;
use App\Models\Dashboard\SiteImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiteImageController extends CrudController
{
    protected static string $modelClass = SiteImage::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'file' => 'file'
    ];
    protected static array $updateRules = [];

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return StreamedResponse
     */
    public function show(int $id): StreamedResponse
    {
        /** @var SiteImage $siteImage */
        $siteImage = SiteImage::query()->find($id);
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
}
