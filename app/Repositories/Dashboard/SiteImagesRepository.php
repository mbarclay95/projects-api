<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Site;
use App\Models\Dashboard\SiteImage;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mbarclay36\LaravelCrud\DefaultRepository;

class SiteImagesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return SiteImage|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $file = $request['file'];
        $path = Storage::disk('s3')->put('site-images', $file);

        $siteImage = new SiteImage([
            's3_path' => $path,
            'original_file_name' => $file->getClientOriginalName()
        ]);
        $siteImage->user()->associate($user);
        $siteImage->save();

        return $siteImage;
    }
}
