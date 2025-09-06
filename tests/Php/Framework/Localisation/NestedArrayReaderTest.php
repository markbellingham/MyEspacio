<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Localisation;

use MyEspacio\Framework\Localisation\NestedArrayReader;
use PHPUnit\Framework\TestCase;

final class NestedArrayReaderTest extends TestCase
{
    public function testConstructor(): void
    {
        $nestedArrayReader = new NestedArrayReader([
            'login' => []
        ]);
        $this->assertCount(1, $nestedArrayReader->getData());
        $this->assertArrayHasKey('login', $nestedArrayReader->getData());
    }

    public function testSetData(): void
    {
        $data = [
            'login' => []
        ];
        $nestedArrayReader = new NestedArrayReader();
        $this->assertCount(0, $nestedArrayReader->getData());

        $nestedArrayReader->setData($data);
        $this->assertCount(1, $nestedArrayReader->getData());
        $this->assertArrayHasKey('login', $nestedArrayReader->getData());
    }

    public function testHasData(): void
    {
        $data = [
            'login' => []
        ];
        $nestedArrayReader = new NestedArrayReader();
        $this->assertFalse($nestedArrayReader->hasData());

        $nestedArrayReader->setData($data);
        $this->assertTrue($nestedArrayReader->hasData());

        $nestedArrayReader->setData([]);
        $this->assertFalse($nestedArrayReader->hasData());
    }

    public function testGetValue(): void
    {
        $nestedArrayReader = new NestedArrayReader([
            'login' => [
                'already_logged_in' => 'You are already logged in',
                'generic_error' => 'Something went wrong, please contact the website administrator',
                'invalid_link' => 'Invalid link. Please try to log in again.',
                'logged_in' => 'You are now logged in',
                'logged_out' => 'You are now logged out',
                'code_sent' => "Please check your %{passcode_route} for the login code",
                'error' => 'Could not log you in.',
                'user_not_found' => 'User not found',
            ]
        ]);

        $value = $nestedArrayReader->getValue(['login','already_logged_in']);
        $this->assertEquals('You are already logged in', $value);
    }

    public function testGetValueUsingPrimaryKey(): void
    {
        $nestedArrayReader = new NestedArrayReader([
            'login' => [
                'already_logged_in' => 'You are already logged in',
                'generic_error' => 'Something went wrong, please contact the website administrator',
                'invalid_link' => 'Invalid link. Please try to log in again.',
                'logged_in' => 'You are now logged in',
                'logged_out' => 'You are now logged out',
                'code_sent' => "Please check your %{passcode_route} for the login code",
                'error' => 'Could not log you in.',
                'user_not_found' => 'User not found',
            ]
        ]);

        $value = $nestedArrayReader->getValue(['login']);
        $this->assertNull($value);
    }

    public function testGetValueUsingNestedKey(): void
    {
        $nestedArrayReader = new NestedArrayReader([
            'login' => [
                'already_logged_in' => 'You are already logged in',
                'generic_error' => 'Something went wrong, please contact the website administrator',
                'invalid_link' => 'Invalid link. Please try to log in again.',
                'logged_in' => 'You are now logged in',
                'logged_out' => 'You are now logged out',
                'code_sent' => "Please check your %{passcode_route} for the login code",
                'error' => 'Could not log you in.',
                'user_not_found' => 'User not found',
            ]
        ]);

        $value = $nestedArrayReader->getValue(['already_logged_in']);
        $this->assertNull($value);
    }

    public function testGetValueUsingWrongKey(): void
    {
        $nestedArrayReader = new NestedArrayReader([
            'login' => [
                'already_logged_in' => 'You are already logged in',
                'generic_error' => 'Something went wrong, please contact the website administrator',
                'invalid_link' => 'Invalid link. Please try to log in again.',
                'logged_in' => 'You are now logged in',
                'logged_out' => 'You are now logged out',
                'code_sent' => "Please check your %{passcode_route} for the login code",
                'error' => 'Could not log you in.',
                'user_not_found' => 'User not found',
            ]
        ]);

        $value = $nestedArrayReader->getValue(['bad_key']);
        $this->assertNull($value);
    }
}
