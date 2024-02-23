<?php

namespace Tests\Feature\Auth;

use App\Enums\Roles;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GeneralAuthTest extends AuthTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testLoggingIn()
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
