<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use Tests\Feature\Auth\AuthTestCase;

class GroceryCategoriesAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_grocery_categories_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/grocery-categories');
    }
}
