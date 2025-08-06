<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('User Model', function () {
    it('can create a user and assign attributes', function () {
        $user = \App\Models\User::factory()->create([
            'name' => 'Unit Test',
            'email' => 'unit@example.com',
            'phone' => '1234567899',
            'role' => \App\Enums\UserRole::ADMIN->value,
        ]);
        expect($user->name)->toBe('Unit Test');
        expect($user->email)->toBe('unit@example.com');
        expect($user->phone)->toBe('1234567899');
        expect($user->role)->toBe(\App\Enums\UserRole::ADMIN);
    });

    it('finds user by email or phone', function () {
        $user = \App\Models\User::factory()->create([
            'email' => 'findme@example.com',
            'phone' => '5555555555',
        ]);
        $byEmail = \App\Models\User::findByEmailOrPhone('findme@example.com');
        $byPhone = \App\Models\User::findByEmailOrPhone('5555555555');
        expect($byEmail->id)->toBe($user->id);
        expect($byPhone->id)->toBe($user->id);
    });

    it('returns correct role value', function () {
        $user = \App\Models\User::factory()->create([
            'role' => \App\Enums\UserRole::SUPER_ADMIN->value,
        ]);
        expect($user->getRoleValue())->toBe('super-admin');
    });

    it('casts email_verified_at to datetime', function () {
        $user = \App\Models\User::factory()->create([
            'email_verified_at' => now(),
        ]);
        expect($user->email_verified_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });
});
