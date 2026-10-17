<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_email_password_reset_is_not_applicable(): void
    {
        $this->markTestSkipped('Gift Shop POS System uses username-based authentication without email.');
    }
}
