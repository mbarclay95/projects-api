<?php

namespace App\Console\Commands\Dashboard;

use App\Models\Dashboard\Folder;
use App\Models\Users\User;
use Illuminate\Console\Command;
use Ramsey\Collection\Collection;

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
        $users = User::query()->get();

        /** @var User $user */
        foreach ($users as $user) {
            /** @var Folder[] $folders */
            $folders = Folder::query()
                             ->where('user_id', '=', $user->id)
                             ->with('sites')
                             ->get();

            $this->recalculateFolderSorting($folders);

            foreach ($folders as $folder) {
                $folder->recalculateSitesSorting();
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @param Collection|Folder[] $folders
     * @return void
     */
    private function recalculateFolderSorting($folders): void
    {
        $sort = 1;
        /** @var Folder $folder */
        foreach ($folders->sortBy('sort') as $folder) {
            if ($folder->sort !== $sort) {
                $folder->sort = $sort;
                $folder->save();
            }
            $sort++;
        }
    }
}
