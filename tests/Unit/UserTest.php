<?php

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('User Model', function () {
    it('creates a user with correct attributes', function () {
        $user = User::factory()->create([
            'name' => 'Unit Test',
            'email' => 'unit@example.com',
            'phone' => '1234567899',
            'role' => UserRole::ADMIN->value,
        ]);
        // Check all attributes in a single array assertion
        expect([
            $user->getAttribute('name'),
            $user->getAttribute('email'),
            $user->getAttribute('phone'),
            $user->getAttribute('role') instanceof UserRole,
            $user->getAttribute('role')->value,
        ])->toMatchArray([
            'Unit Test',
            'unit@example.com',
            '1234567899',
            true,
            UserRole::ADMIN->value,
        ]);
    });

    it('finds user by email or phone', function () {
        $user = User::factory()->create([
            'email' => 'findme@example.com',
            'phone' => '5555555555',
        ]);
        foreach ([
            'findme@example.com',
            '5555555555',
        ] as $identifier) {
            $foundUser = User::findByEmailOrPhone($identifier);
            expect([
                $foundUser->getAttribute('id'),
                $foundUser->getAttribute('phone'),
                $foundUser->getAttribute('email'),
            ])->toMatchArray([
                $user->getAttribute('id'),
                $user->getAttribute('phone'),
                $user->getAttribute('email'),
            ]);
        }
    });

    it('returns correct role value', function () {
        $user = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN->value,
        ]);
        expect($user->getRoleValue())->toBe('super-admin');
    });

    it('casts email_verified_at to Carbon instance', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        expect($user->getAttribute('email_verified_at'))->toBeInstanceOf(Carbon::class);
    });
});
