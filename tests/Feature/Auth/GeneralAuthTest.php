<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GeneralAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_logging_in()
    {
        User::factory()->create([
            'username' => 'mbarclay',
            'password' => Hash::make('testing123')
        ]);

        $goodResponse = $this->post('/api/login', [
            'username' => 'mbarclay',
            'password' => 'testing123'
        ]);
        $goodResponse->assertSuccessful();

        $badResponse = $this->post('/api/login', [
            'username' => 'mbarclay',
            'password' => 'incorrect'
        ]);
        $badResponse->assertUnauthorized();
    }
}
