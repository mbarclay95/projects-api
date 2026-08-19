<?php

namespace Tests\Unit\Drafts;

use App\Enums\DraftStatus;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Users\User;
use Tests\TestCase;

class DraftModelTest extends TestCase
{
    /**
     * The draft read returns every member, so a secret in the payload hands
     * everyone else's credential to anyone holding the link.
     *
     * This is the backend payload. The public one lives in projects-api-public
     * and does not exist until stage 4, so what this guards is
     * $apiModelAttributes — the thing stage 4 mirrors.
     */
    public function test_a_members_secret_is_never_in_the_api_payload()
    {
        $draft = $this->createDraft();
        $member = DraftMember::factory()->create([
            'draft_id' => $draft->id,
            'secret' => 'a-real-looking-secret',
        ]);

        $memberPayload = DraftMember::toApiModel($member);

        self::assertArrayNotHasKey('secret', $memberPayload);
        self::assertEquals('a-real-looking-secret', $member->secret, 'the column itself should still be readable');

        // The nested render is the one that actually ships, so check the whole
        // draft payload rather than the member in isolation.
        self::assertStringNotContainsString(
            'a-real-looking-secret',
            json_encode(Draft::toApiModel($draft->fresh()))
        );
    }

    /**
     * The invariant the whole multi-admin model rests on: a draft that exists
     * with no admin row is not a state the application can produce.
     */
    public function test_creating_a_draft_leaves_the_creator_as_the_only_admin()
    {
        $creator = User::factory()->create();

        $draft = Draft::createEntity(['name' => 'Fantasy 2026'], $creator);

        self::assertEquals(1, $draft->draftAdmins()->count());
        self::assertEquals($creator->id, $draft->draftAdmins()->first()->user_id);
        self::assertEquals($creator->id, $draft->created_by_id);
        self::assertEquals(DraftStatus::SIGNUP, $draft->fresh()->status);
        self::assertNotEmpty($draft->token);
    }

    /**
     * getEntities() scopes by draft_admins membership rather than by a column,
     * and that subquery is the only thing between one admin's drafts and
     * another's. The negative half is the one that matters — it is what would
     * catch the scoping being silently dropped.
     */
    public function test_drafts_are_scoped_to_their_admins()
    {
        $creator = User::factory()->create();
        $coAdmin = User::factory()->create();
        $stranger = User::factory()->create();

        $draft = Draft::createEntity(['name' => 'Shared'], $creator);
        $draft->draftAdmins()->create(['user_id' => $coAdmin->id]);
        Draft::createEntity(['name' => 'Not Yours'], $stranger);

        self::assertEquals(['Shared'], $this->visibleDraftNames($creator));
        self::assertEquals(['Shared'], $this->visibleDraftNames($coAdmin), 'a co-admin should see a draft it did not create');
        self::assertEquals(['Not Yours'], $this->visibleDraftNames($stranger), 'a stranger should see neither');
    }

    /**
     * Removing a co-admin should revoke visibility, since membership is the
     * whole access rule.
     */
    public function test_removing_an_admin_removes_their_access()
    {
        $creator = User::factory()->create();
        $coAdmin = User::factory()->create();

        $draft = Draft::createEntity(['name' => 'Shared'], $creator);
        $adminRow = $draft->draftAdmins()->create(['user_id' => $coAdmin->id]);
        self::assertEquals(['Shared'], $this->visibleDraftNames($coAdmin));

        $adminRow->delete();

        self::assertEquals([], $this->visibleDraftNames($coAdmin));
        self::assertEquals(['Shared'], $this->visibleDraftNames($creator));
    }

    /**
     * @return string[]
     */
    private function visibleDraftNames(User $user): array
    {
        return Draft::getEntities([], $user, true)->pluck('name')->all();
    }

    private function createDraft(): Draft
    {
        return Draft::factory()->create([
            'created_by_id' => User::factory()->create()->id,
        ]);
    }
}
