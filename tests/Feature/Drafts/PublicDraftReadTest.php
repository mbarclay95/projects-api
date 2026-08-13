<?php

namespace Tests\Feature\Drafts;

use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftAdmin;
use App\Models\Drafts\DraftImage;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftTeam;
use App\Models\Users\User;
use Tests\TestCase;

/**
 * `GET public/drafts/{draftId}` — the payload shape and the token gate
 * shared by all four public routes.
 */
class PublicDraftReadTest extends TestCase
{
    private Draft $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $this->draft = Draft::factory()->create(['created_by_id' => $admin->id]);
        DraftAdmin::factory()->create(['draft_id' => $this->draft->id, 'user_id' => $admin->id]);
        DraftTeam::factory()->create(['draft_id' => $this->draft->id, 's3_path' => 'draft-teams/logo.png']);
        DraftMember::factory()->create(['draft_id' => $this->draft->id]);
    }

    /**
     * One test per leaking key, so a failure names exactly which one leaked
     * rather than a single assertion covering all four going quietly stale.
     */
    public function testPayloadContainsNoToken(): void
    {
        self::assertStringNotContainsString('"token"', $this->show()->getContent());
    }

    public function testPayloadContainsNoS3Path(): void
    {
        $content = $this->show()->getContent();
        self::assertStringNotContainsString('s3_path', $content);
        self::assertStringNotContainsString('s3Path', $content);
    }

    public function testPayloadContainsNoSecret(): void
    {
        self::assertStringNotContainsString('"secret"', $this->show()->getContent());
    }

    public function testPayloadContainsNoDraftAdmins(): void
    {
        self::assertStringNotContainsString('draftAdmins', $this->show()->getContent());
    }

    public function testPayloadReportsNoImageWhenTheDraftHasNone(): void
    {
        $this->show()->assertJsonFragment(['hasImage' => false]);
    }

    /**
     * A boolean rather than the id, matching the draftTeams entries: the
     * public page addresses the image through the draft it already knows.
     */
    public function testPayloadReportsAnImageWithoutRevealingIt(): void
    {
        $draftImage = DraftImage::factory()->create(['created_by_id' => $this->draft->created_by_id]);
        $this->draft->draft_image_id = $draftImage->id;
        $this->draft->save();

        $content = $this->show()->assertJsonFragment(['hasImage' => true])->getContent();
        self::assertStringNotContainsString('draftImageId', $content);
    }

    public function testAWrongTokenIsA404OnAllFivePublicRoutes(): void
    {
        $member = DraftMember::factory()->create(['draft_id' => $this->draft->id]);
        $team = DraftTeam::factory()->create(['draft_id' => $this->draft->id]);

        $this->getJson("api/public/drafts/{$this->draft->id}?token=wrong")
             ->assertStatus(404);

        $this->postJson('api/public/draft-members', [
            'draftId' => $this->draft->id,
            'token' => 'wrong',
            'name' => 'Newcomer',
        ])->assertStatus(404);

        $this->postJson('api/public/draft-picks', [
            'draftId' => $this->draft->id,
            'token' => 'wrong',
            'secret' => $member->secret,
            'draftTeamId' => $team->id,
        ])->assertStatus(404);

        $this->getJson("api/public/draft-teams/{$team->id}/image?token=wrong")
             ->assertStatus(404);

        $this->getJson("api/public/drafts/{$this->draft->id}/image?token=wrong")
             ->assertStatus(404);
    }

    private function show()
    {
        return $this->getJson("api/public/drafts/{$this->draft->id}?token={$this->draft->token}")
                    ->assertSuccessful();
    }
}
