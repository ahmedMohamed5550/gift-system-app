<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    public function test_email_verification_is_not_applicable(): void
    {
        $this->markTestSkipped('Gift Shop POS System uses username-based authentication without email.');
    }
}
