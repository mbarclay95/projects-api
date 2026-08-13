<?php

namespace Tests\Feature\Drafts;

use App\Enums\Roles;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftImage;
use App\Models\Users\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The draft image upload and the two streams. Unlike a team logo, an image is
 * uploaded before the draft exists and attached by id on save, so the
 * interesting assertions are about that attach/detach round trip.
 */
class DraftImageTest extends TestCase
{
    private User $organizer;

    private User $stranger;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('minio-s3');

        $this->organizer = User::factory()->create();
        $this->organizer->assignRole(Roles::DRAFTS_ROLE);
        $this->stranger = User::factory()->create();
    }

    public function testAnOrganizerCanUploadAnImageWithNoDraftInHand(): void
    {
        $response = $this->uploadAs($this->organizer)->assertSuccessful();

        $draftImage = DraftImage::query()->findOrFail($response->json('id'));
        self::assertEquals('logo.png', $draftImage->original_file_name);
        self::assertEquals($this->organizer->id, $draftImage->created_by_id);
        Storage::disk('minio-s3')->assertExists($draftImage->s3_path);
    }

    /**
     * The upload has no draft to scope to, so Draft::createPermission() is the
     * whole gate — a user without it must not be able to write to the bucket.
     */
    public function testAUserWithoutTheDraftsRoleCannotUpload(): void
    {
        $this->uploadAs($this->stranger)->assertUnauthorized();

        self::assertEquals(0, DraftImage::query()->count());
    }

    public function testTheStoragePathIsNotExposedInTheUploadResponse(): void
    {
        $content = $this->uploadAs($this->organizer)->assertSuccessful()->getContent();

        self::assertStringNotContainsString('s3_path', $content);
        self::assertStringNotContainsString('s3Path', $content);
    }

    public function testAnImageCanBeAttachedWhenTheDraftIsCreated(): void
    {
        $draftImage = DraftImage::factory()->create(['created_by_id' => $this->organizer->id]);

        $response = $this->jsonAs($this->organizer, 'POST', 'api/drafts', [
            'name' => 'Imaged Draft',
            'draftImageId' => $draftImage->id,
        ])->assertSuccessful();

        $draft = Draft::query()->findOrFail($response->json('id'));
        self::assertEquals($draftImage->id, $draft->draft_image_id);
    }

    /**
     * Remove is a null on update rather than an endpoint of its own, so this
     * is the only thing standing between the organizer and a stuck image.
     */
    public function testAnImageCanBeClearedOnUpdate(): void
    {
        $draft = $this->draftWithImage();

        $this->jsonAs($this->organizer, 'PUT', "api/drafts/{$draft->id}", [
            'name' => $draft->name,
            'draftImageId' => null,
        ])->assertSuccessful();

        self::assertNull($draft->fresh()->draft_image_id);
    }

    public function testAnUnknownImageIdIsRejected(): void
    {
        $this->jsonAs($this->organizer, 'POST', 'api/drafts', [
            'name' => 'Imaged Draft',
            'draftImageId' => 9999,
        ])->assertStatus(422);
    }

    public function testTheAdminStreamServesTheImageWithAContentTypeFromItsExtension(): void
    {
        $draftImage = DraftImage::factory()->create([
            'created_by_id' => $this->organizer->id,
            's3_path' => 'draft-images/logo.svg',
        ]);
        Storage::disk('minio-s3')->put('draft-images/logo.svg', '<svg></svg>');

        $this->get("api/draft-images/{$draftImage->id}")
             ->assertSuccessful()
             ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function testTheAdminStreamIs404ForAnUnknownImage(): void
    {
        $this->get('api/draft-images/9999')->assertStatus(404);
    }

    public function testThePublicStreamServesTheImageToATokenHolder(): void
    {
        $draft = $this->draftWithImage();
        Storage::disk('minio-s3')->put($draft->draftImage->s3_path, 'png-bytes');

        $this->get("api/public/drafts/{$draft->id}/image?token={$draft->token}")
             ->assertSuccessful()
             ->assertHeader('Content-Type', 'image/png');
    }

    public function testThePublicStreamIs404ForAWrongToken(): void
    {
        $draft = $this->draftWithImage();

        $this->getJson("api/public/drafts/{$draft->id}/image?token=wrong")->assertStatus(404);
    }

    /**
     * A draft with no image 404s rather than streaming nothing, so the public
     * page's hasImage flag and this route cannot disagree visibly.
     */
    public function testThePublicStreamIs404WhenTheDraftHasNoImage(): void
    {
        $draft = Draft::createEntity(['name' => 'Bare Draft'], $this->organizer);

        $this->getJson("api/public/drafts/{$draft->id}/image?token={$draft->token}")->assertStatus(404);
    }

    private function draftWithImage(): Draft
    {
        $draftImage = DraftImage::factory()->create(['created_by_id' => $this->organizer->id]);

        return Draft::createEntity([
            'name' => 'Imaged Draft',
            'draftImageId' => $draftImage->id,
        ], $this->organizer);
    }

    private function uploadAs(User $user): TestResponse
    {
        // create() rather than image(): the test container has no GD.
        return $this->jsonAs($user, 'POST', 'api/draft-images', [
            'file' => UploadedFile::fake()->create('logo.png', 16, 'image/png'),
        ]);
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
