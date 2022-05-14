<?php

namespace App\Console\Commands\Dashboard;

use App\Models\Dashboard\Folder;
use App\Models\Dashboard\Site;
use App\Models\Dashboard\SiteImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MigrateDbModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:migrate-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//        $this->saveFolders();
//        $this->saveSites();
        $this->saveSiteImages();
    }

    private function saveFolders()
    {
        $oldModel = new Folder();
        $oldModel->setConnection('pgsql2');
        /** @var Folder[] $oldModels */
        $oldModels = $oldModel->get();

        foreach ($oldModels as $folder) {
            $newFolder = new Folder([
                'sort' => $folder->sort,
                'name' => $folder->name,
                'show' => $folder->show,
                'user_id' => 1,
            ]);
            $newFolder->save();
        }
    }

    private function saveSites()
    {
        $oldModel = new Site();
        $oldModel->setConnection('pgsql2');
        /** @var Site[] $oldModels */
        $oldModels = $oldModel->get();

        foreach ($oldModels as $site) {
            $newSite = new Site([
                'sort' => $site->sort,
                'name' => $site->name,
                'show' => $site->show,
                'description' => $site->description,
                'url' => $site->url,
                'user_id' => 1,
                'folder_id' => $site->folder_id,
                'site_image_id' => $site->site_image_id,
            ]);
            $newSite->save();
        }
    }

    private function saveSiteImages()
    {
        $oldModel = new SiteImage();
        $oldModel->setConnection('pgsql2');
        /** @var SiteImage[] $oldModels */
        $oldModels = $oldModel->get();

        foreach ($oldModels as $siteImage) {
            $newSiteImage = new SiteImage([
                'id' => $siteImage->id,
                'original_file_name' => $siteImage->original_file_name,
                's3_path' => $siteImage->s3_path,
                'user_id' => 1,
            ]);
            $newSiteImage->save();
        }
    }
}
