<?php

describe('User Registration', function () {
    it('registers a user with phone and email', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'phone', 'role'],
                'token',
            ]);
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'phone' => '1234567890',
        ]);
    });

    it('fails registration without phone', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'testuser2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(422);
    });

    it('fails registration without email', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'phone' => '1234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(422);
    });
});

describe('User Login', function () {
    beforeEach(function () {
        \App\Models\User::factory()->create([
            'name' => 'Login User',
            'email' => 'login@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);
    });

    it('logs in with email', function () {
        $response = $this->postJson('/api/login', [
            'identifier' => 'login@example.com',
            'password' => 'secret123',
        ]);
        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'phone', 'role'],
                'token',
            ]);
    });

    it('logs in with phone', function () {
        $response = $this->postJson('/api/login', [
            'identifier' => '9999999999',
            'password' => 'secret123',
        ]);
        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'phone', 'role'],
                'token',
            ]);
    });

    it('fails login with wrong password', function () {
        $response = $this->postJson('/api/login', [
            'identifier' => 'login@example.com',
            'password' => 'wrongpass',
        ]);
        $response->assertStatus(401);
    });
});

describe('Notifications', function () {
    it('stores notification in database after registration', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'Notify User',
            'email' => 'notify@example.com',
            'phone' => '8888888888',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $userId = $response->json('user.id');
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $userId,
            'type' => \App\Notifications\NewUserRegistered::class,
        ]);
    });
});
