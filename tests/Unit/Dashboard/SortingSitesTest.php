<?php

namespace Tests\Unit\Dashboard;

use App\Models\Dashboard\Folder;
use App\Models\Dashboard\Site;
use App\Models\Users\User;
use App\Repositories\Dashboard\SitesRepository;
use Tests\TestCase;

class SortingSitesTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testUpdatingAndDestroyingSites()
    {
        $user = User::factory()->create();
        $siteIdsKeyedBySort = $this->createSites($user);

        //-------------------------------------------------
        // setting show to false should clear out sort
        /** @var Site $site2 */
        $site2 = Site::query()->find($siteIdsKeyedBySort[2]);
        SitesRepository::updateEntityStatic($site2, [
            'name' => $site2->name,
            'description' => $site2->description,
            'show' => false,
            'url' => $site2->url,
            'folderId' => $site2->folder_id,
            'siteImage' => null
        ], $user);
        self::assertEquals(1, $this->getSort($siteIdsKeyedBySort[1]));
        self::assertEquals(null, $this->getSort($siteIdsKeyedBySort[2]));
        self::assertEquals(2, $this->getSort($siteIdsKeyedBySort[3]));
        //-------------------------------------------------


        //-------------------------------------------------
        // setting show to true should set sort to last
        /** @var Site $site2 */
        $site2 = Site::query()->find($siteIdsKeyedBySort[2]);
        SitesRepository::updateEntityStatic($site2, [
            'name' => $site2->name,
            'description' => $site2->description,
            'show' => true,
            'url' => $site2->url,
            'folderId' => $site2->folder_id,
            'siteImage' => null
        ], $user);
        self::assertEquals(1, $this->getSort($siteIdsKeyedBySort[1]));
        self::assertEquals(3, $this->getSort($siteIdsKeyedBySort[2]));
        self::assertEquals(2, $this->getSort($siteIdsKeyedBySort[3]));
        //-------------------------------------------------

        //-------------------------------------------------
        // setting folderId to new folder should recalculate sort
        $newFolder = Folder::factory()->create([
            'user_id' => $user->id
        ]);
        /** @var Site $site1 */
        $site1 = Site::query()->find($siteIdsKeyedBySort[1]);
        SitesRepository::updateEntityStatic($site1, [
            'name' => $site1->name,
            'description' => $site1->description,
            'show' => true,
            'url' => $site1->url,
            'folderId' => $newFolder->id,
            'siteImage' => null
        ], $user);
        self::assertEquals(1, $this->getSort($siteIdsKeyedBySort[1]));
        self::assertEquals(2, $this->getSort($siteIdsKeyedBySort[2]));
        self::assertEquals(1, $this->getSort($siteIdsKeyedBySort[3]));
        //-------------------------------------------------

        //-------------------------------------------------
        // setting folderId back to old folder should recalculate sort
        /** @var Site $site1 */
        $site1 = Site::query()->find($siteIdsKeyedBySort[1]);
        SitesRepository::updateEntityStatic($site1, [
            'name' => $site1->name,
            'description' => $site1->description,
            'show' => true,
            'url' => $site1->url,
            'folderId' => $site2->folder_id,
            'siteImage' => null
        ], $user);
        self::assertEquals(3, $this->getSort($siteIdsKeyedBySort[1]));
        self::assertEquals(2, $this->getSort($siteIdsKeyedBySort[2]));
        self::assertEquals(1, $this->getSort($siteIdsKeyedBySort[3]));
        //-------------------------------------------------

        //-------------------------------------------------
        // should not be possible to set higher than max sort
        SitesRepository::updateSitesSorts([
            'folderId' => $site2->folder_id,
            'data' => [
                [
                    'sort' => 5,
                    'id' => $siteIdsKeyedBySort[1]
                ]
            ]
        ], $user);
        self::assertEquals(3, $this->getSort($siteIdsKeyedBySort[1]));
        self::assertEquals(2, $this->getSort($siteIdsKeyedBySort[2]));
        self::assertEquals(1, $this->getSort($siteIdsKeyedBySort[3]));
        //-------------------------------------------------

        //-------------------------------------------------
        // Destroying site should recalculate sort
        /** @var Site $site3 */
        $site3 = Site::query()->find($siteIdsKeyedBySort[3]);
        SitesRepository::destroyEntityStatic($site3, $user);
        self::assertEquals(2, $this->getSort($siteIdsKeyedBySort[1]));
        self::assertEquals(1, $this->getSort($siteIdsKeyedBySort[2]));
        self::assertDatabaseMissing('sites', ['id' => $siteIdsKeyedBySort[3]]);
        //-------------------------------------------------
    }

    private function getSort(int $siteId): null | int {
        return Site::query()->find($siteId)->sort;
    }

    private function createSites(User $user): array
    {
        $folder = Folder::factory()->create([
            'user_id' => $user->id
        ]);
        $site1 = Site::factory()->create([
            'sort' => 1,
            'folder_id' => $folder->id,
            'user_id' => $user->id
        ]);
        $site2 = Site::factory()->create([
            'sort' => 2,
            'folder_id' => $folder->id,
            'user_id' => $user->id
        ]);
        $site3 = Site::factory()->create([
            'sort' => 3,
            'folder_id' => $folder->id,
            'user_id' => $user->id
        ]);

        return [
            1 => $site1->id,
            2 => $site2->id,
            3 => $site3->id,
        ];
    }
}
