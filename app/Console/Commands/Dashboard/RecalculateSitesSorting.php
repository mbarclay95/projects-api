<?php

namespace App\Console\Commands\Dashboard;

use App\Models\Dashboard\Folder;
use Illuminate\Console\Command;

class RecalculateSitesSorting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:recalculate-site-sorting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var Folder[] $folders */
        $folders = Folder::query()
                         ->with('sites')
                         ->get();

        foreach ($folders as $folder) {
            $folder->recalculateSitesSorting();
        }

        return Command::SUCCESS;
    }
}
