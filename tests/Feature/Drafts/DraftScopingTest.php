<?php

namespace Tests\Feature\Drafts;

use App\Enums\Roles;
use App\Models\Drafts\Draft;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DraftScopingTest extends TestCase
{
    private User $creator;

    private User $coAdmin;

    private User $stranger;

    private Draft $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create();
        $this->coAdmin = User::factory()->create();
        $this->stranger = User::factory()->create();
        foreach ([$this->creator, $this->coAdmin, $this->stranger] as $user) {
            $user->assignRole(Roles::DRAFTS_ROLE);
        }

        $this->draft = Draft::createEntity(['name' => 'Owned Draft'], $this->creator);
        // The stranger administers a draft of their own — the assertions below
        // are about draft_admins membership, not about holding drafts_role at all.
        Draft::createEntity(['name' => "Stranger's Draft"], $this->stranger);
    }

    /**
     * The assertion the _for_user hazard exists for — see schema.md. A
     * cannotUpdate() that fell back to the package's unscoped $model->user_id
     * comparison would 403 here regardless, so this alone wouldn't catch a
     * regression to "any drafts_role holder can edit any draft" either; that
     * is what testASecondAdminCanReadAndUpdateTheDraft is for.
     */
    public function testOneAdminCannotUpdateOrDestroyAnotherAdminsDraft(): void
    {
        $this->jsonAs($this->stranger, 'PUT', "api/drafts/{$this->draft->id}", [
            'name' => 'Hijacked',
        ])->assertUnauthorized();

        $this->jsonAs($this->stranger, 'DELETE', "api/drafts/{$this->draft->id}")
            ->assertUnauthorized();

        self::assertEquals('Owned Draft', $this->draft->fresh()->name);
        self::assertNull($this->draft->fresh()->deleted_at);
    }

    public function testASecondAdminCanReadAndUpdateTheDraft(): void
    {
        $this->draft->draftAdmins()->create(['user_id' => $this->coAdmin->id]);

        $this->jsonAs($this->coAdmin, 'PUT', "api/drafts/{$this->draft->id}", [
            'name' => 'Renamed by co-admin',
        ])->assertSuccessful()
          ->assertJsonFragment(['name' => 'Renamed by co-admin']);

        $names = array_column(
            $this->jsonAs($this->coAdmin, 'GET', 'api/drafts?showArchived=0')
                 ->assertSuccessful()
                 ->json(),
            'name'
        );
        self::assertContains('Renamed by co-admin', $names, 'a co-admin should see a draft it did not create');
    }

    /**
     * The whereHas() scoping in Draft::getEntities() — the one the doc warns
     * is easy to "simplify" away.
     */
    public function testAThirdUserHoldingDraftsRoleSeesNeitherDraftInIndex(): void
    {
        $this->draft->draftAdmins()->create(['user_id' => $this->coAdmin->id]);

        $response = $this->jsonAs($this->stranger, 'GET', 'api/drafts?showArchived=0')->assertSuccessful();
        $names = array_column($response->json(), 'name');

        self::assertNotContains('Owned Draft', $names);
    }

    public function testShowIs404ForAUserWhoDoesNotAdministerTheDraft(): void
    {
        $this->jsonAs($this->stranger, 'GET', "api/drafts/{$this->draft->id}")->assertStatus(404);
    }

    public function testShowSucceedsForAnAdminOfTheDraft(): void
    {
        $this->jsonAs($this->creator, 'GET', "api/drafts/{$this->draft->id}")
             ->assertSuccessful()
             ->assertJsonFragment(['name' => 'Owned Draft']);
    }

    /**
     * Decision D in one assertion, on draft-teams; DraftChildController means
     * the other three child controllers follow the same path.
     */
    public function testAChildCreateAgainstADraftYouDoNotAdministerIsRefused(): void
    {
        $this->jsonAs($this->stranger, 'POST', 'api/draft-teams', [
            'draftId' => $this->draft->id,
            'name' => 'Sneaky Team',
        ])->assertUnauthorized();

        self::assertEquals(0, $this->draft->draftTeams()->count());
    }

    private function jsonAs(User $user, string $method, string $uri, array $data = []): TestResponse
    {
        $token = auth()->login($user);

        return $this->actingAs($user)->json($method, $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token,
        ]);
    }
}
