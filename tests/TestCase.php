<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\InteractsWithExceptionHandling;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,
        DatabaseMigrations,
        InteractsWithExceptionHandling;

    protected function setUp()
    {
        parent::setUp();

        $this->seed();

        Schema::disableForeignKeyConstraints();
    }

    public function makeAdminUser()
    {
        return $this->makeUser('admin');
    }

    public function makeUser($roles = 'user')
    {
        $user = factory(User::class)->create();

        $user->attachRoles((array)$roles);

        return $user;
    }
}
