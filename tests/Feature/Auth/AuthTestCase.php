<?php

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTestCase extends TestCase
{
    use RefreshDatabase;

    protected User|null $goodUser = null;

    protected User|null $badUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $goodUser */
        $goodUser = User::factory()->create();
        $this->goodUser = $goodUser;

        /** @var User $badUser */
        $badUser = User::factory()->create();
        $this->badUser = $badUser;
    }

    protected function initRoles(array $goodRoles, array $badRoles): void
    {
        $this->goodUser->assignRole($goodRoles);
        $this->badUser->assignRole($badRoles);
    }

    protected function runTestsGET(string $endpoint): void
    {
        $token = auth()->login($this->goodUser);
        $goodResponse = $this->actingAs($this->goodUser)
                             ->get($endpoint, [
                                 'accept' => 'application/json',
                                 'authorization' => 'Bearer ' . $token
                             ]);
        $this->assertContains($goodResponse->status(), [200, 422]);

        $token = auth()->login($this->badUser);
        $this->get($endpoint, [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token
        ])
             ->assertUnauthorized();
    }

    protected function runTestsPOST(string $endpoint): void
    {
        $token = auth()->login($this->goodUser);
        $goodResponse = $this->actingAs($this->goodUser)
                             ->post($endpoint, [], [
                                 'accept' => 'application/json',
                                 'authorization' => 'Bearer ' . $token
                             ]);
        $this->assertContains($goodResponse->status(), [200, 422]);

        $token = auth()->login($this->badUser);
        $this->post($endpoint, [], [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token
        ])
             ->assertUnauthorized();
    }

    protected function runTestsPUT(string $endpoint): void
    {
        $token = auth()->login($this->goodUser);
        $goodResponse = $this->actingAs($this->goodUser)
                             ->put($endpoint, [], [
                                 'accept' => 'application/json',
                                 'authorization' => 'Bearer ' . $token
                             ]);
        $this->assertContains($goodResponse->status(), [200, 422]);

        $token = auth()->login($this->badUser);
        $this->put($endpoint, [], [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token
        ])
             ->assertUnauthorized();
    }

    protected function runTestsDELETE(string $endpoint): void
    {
        $token = auth()->login($this->badUser);
        $this->delete($endpoint, [], [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token
        ])
             ->assertUnauthorized();

        $token = auth()->login($this->goodUser);
        $goodResponse = $this->actingAs($this->goodUser)
                             ->delete($endpoint, [], [
                                 'accept' => 'application/json',
                                 'authorization' => 'Bearer ' . $token
                             ]);
        $this->assertContains($goodResponse->status(), [200, 422]);
    }

}
