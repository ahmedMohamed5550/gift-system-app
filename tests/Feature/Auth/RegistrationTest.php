<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_public_registration_is_not_applicable(): void
    {
        $this->markTestSkipped('Gift Shop POS System accounts are provisioned internally without public registration.');
    }
}
