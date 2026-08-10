<?php

namespace Tests\Feature\Drafts;

use App\Enums\DraftStatus;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Users\User;
use Tests\TestCase;

/**
 * `POST public/draft-members` — claim a name.
 */
class PublicDraftMemberClaimTest extends TestCase
{
    public function testADuplicateNameIsRejectedCaseInsensitively(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::SIGNUP]);
        DraftMember::factory()->create(['draft_id' => $draft->id, 'name' => 'Mike']);

        $this->claim($draft, 'mike')->assertStatus(422);
        self::assertEquals(1, $draft->draftMembers()->count());
    }

    /**
     * The cap is rejected at the boundary, not one past it: with one spot
     * left, the claim that fills it must succeed, and only the next one
     * after that is refused.
     */
    public function testTheParticipantCapIsRejectedAtTheBoundaryNotOnePastIt(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::SIGNUP, 'max_participants' => 2]);
        DraftMember::factory()->create(['draft_id' => $draft->id]);

        $this->claim($draft, 'Last Spot')->assertSuccessful();
        self::assertEquals(2, $draft->draftMembers()->count());

        $this->claim($draft, 'One Too Many')->assertStatus(422);
        self::assertEquals(2, $draft->draftMembers()->count());
    }

    public function testAClaimDuringLockedIsRejected(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::LOCKED]);

        $this->claim($draft, 'Anyone')->assertStatus(422);
        self::assertEquals(0, $draft->draftMembers()->count());
    }

    private function createDraft(array $overrides = []): Draft
    {
        return Draft::factory()->create(array_merge(
            ['created_by_id' => User::factory()->create()->id],
            $overrides
        ));
    }

    private function claim(Draft $draft, string $name)
    {
        return $this->postJson('api/public/draft-members', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'name' => $name,
        ]);
    }
}
