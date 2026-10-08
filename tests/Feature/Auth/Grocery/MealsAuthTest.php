<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use Tests\Feature\Auth\AuthTestCase;

class MealsAuthTest extends AuthTestCase
{
    public function test_get_meals_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/meals');
    }

    public function test_put_meals_day_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsPUT('api/meals/days/2026-10-12');
    }
}
