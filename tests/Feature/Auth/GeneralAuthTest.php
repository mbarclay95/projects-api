<?php

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;

class GeneralAuthTest extends AuthTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_logging_in()
    {
        User::factory()->create([
            'username' => 'mbarclay',
            'password' => Hash::make('testing123'),
        ]);

        $goodResponse = $this->post('/api/login', [
            'username' => 'mbarclay',
            'password' => 'testing123',
        ]);
        $goodResponse->assertSuccessful();

        $badResponse = $this->post('/api/login', [
            'username' => 'mbarclay',
            'password' => 'incorrect',
        ]);
        $badResponse->assertUnauthorized();
    }
}
