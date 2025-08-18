<?php

declare(strict_types=1);

namespace Tests\User\Domain;

use MyEspacio\User\Domain\PasscodeRoute;
use PHPUnit\Framework\TestCase;

final class PasscodeRouteTest extends TestCase
{
    public function testEnum(): void
    {
        $expected = [
            'Phone' => 'phone',
            'Email' => 'email',
        ];

        $actual = [];
        foreach (PasscodeRoute::cases() as $case) {
            $actual[$case->name] = $case->value;
        }

        $this->assertEquals($expected, $actual);
    }
}
