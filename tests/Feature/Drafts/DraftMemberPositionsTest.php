<?php

namespace Tests\Feature\Drafts;

use App\Enums\Roles;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Users\User;
use Tests\TestCase;

class DraftMemberPositionsTest extends TestCase
{
    private User $admin;

    private Draft $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::DRAFTS_ROLE);
        $this->draft = Draft::createEntity(['name' => 'Order Test'], $this->admin);
    }

    /**
     * The two-phase null-then-set update only helps once the list is known
     * good — an incomplete list must never reach it, or a draft short one
     * member from being ordered would come back reporting as ordered.
     */
    public function test_a_partial_list_is_rejected_and_leaves_no_member_reordered(): void
    {
        $memberA = DraftMember::factory()->create(['draft_id' => $this->draft->id]);
        $memberB = DraftMember::factory()->create(['draft_id' => $this->draft->id]);
        $memberC = DraftMember::factory()->create(['draft_id' => $this->draft->id]);

        $response = $this->patchPositions([
            ['draftMemberId' => $memberA->id, 'pickPosition' => 1],
            ['draftMemberId' => $memberB->id, 'pickPosition' => 2],
            // memberC omitted on purpose
        ]);

        $response->assertStatus(422);
        self::assertNull($memberA->fresh()->pick_position);
        self::assertNull($memberB->fresh()->pick_position);
        self::assertNull($memberC->fresh()->pick_position);
    }

    public function test_a_gap_in_positions_is_rejected(): void
    {
        $memberA = DraftMember::factory()->create(['draft_id' => $this->draft->id]);
        $memberB = DraftMember::factory()->create(['draft_id' => $this->draft->id]);

        $this->patchPositions([
            ['draftMemberId' => $memberA->id, 'pickPosition' => 1],
            ['draftMemberId' => $memberB->id, 'pickPosition' => 3],
        ])->assertStatus(422);
    }

    public function test_a_full_valid_list_succeeds_and_swaps_cleanly(): void
    {
        $memberA = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'pick_position' => 1]);
        $memberB = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'pick_position' => 2]);

        // Swapping two already-assigned positions is exactly the case that
        // would violate unique(draft_id, pick_position) transiently without
        // the null-then-set two-phase update.
        $this->patchPositions([
            ['draftMemberId' => $memberA->id, 'pickPosition' => 2],
            ['draftMemberId' => $memberB->id, 'pickPosition' => 1],
        ])->assertSuccessful();

        self::assertEquals(2, $memberA->fresh()->pick_position);
        self::assertEquals(1, $memberB->fresh()->pick_position);
    }

    private function patchPositions(array $positions)
    {
        $token = auth()->login($this->admin);

        return $this->actingAs($this->admin)->json('PATCH', 'api/draft-member-positions', [
            'draftId' => $this->draft->id,
            'positions' => $positions,
        ], [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$token,
        ]);
    }
}
