<?php

use App\Models\User;
use App\Notifications\NewUserRegistered;

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
        User::factory()->create([
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
            'password' => 'wrong password',
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
            'type' => NewUserRegistered::class,
        ]);
    });
});

describe('Password Reset', function () {
    it('sends password reset link to email', function () {
        $user = User::factory()->create([
            'email' => 'resetme@example.com',
        ]);
        $response = $this->postJson('/api/request-password-reset', [
            'email' => 'resetme@example.com',
        ]);
        $response->assertOk()->assertJson(['message' => 'Password reset link sent to email.']);
    });

    it('sends password reset code to phone', function () {
        $user = User::factory()->create([
            'phone' => '1234567899',
        ]);
        $response = $this->postJson('/api/request-password-reset', [
            'phone' => '1234567899',
        ]);
        $response->assertOk()->assertJsonStructure(['message', 'code']);
    });

    it('resets password with valid email token', function () {
        $user = User::factory()->create([
            'email' => 'resetme2@example.com',
        ]);
        $token = app('auth.password.broker')->createToken($user);
        $response = $this->postJson('/api/reset-password', [
            'email' => 'resetme2@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $response->assertOk()->assertJson(['message' => 'Password has been reset.']);
    });

    it('resets password with valid phone code', function () {
        $user = User::factory()->create([
            'phone' => '1234567898',
        ]);
        // Request code
        $response = $this->postJson('/api/request-password-reset', [
            'phone' => '1234567898',
        ]);
        $code = $response->json('code');
        // Reset password
        $resetResponse = $this->postJson('/api/reset-password', [
            'phone' => '1234567898',
            'code' => (string) $code,
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);
        $resetResponse->assertOk()->assertJson(['message' => 'Password has been reset.']);
    });

    it('fails to reset password with invalid phone code', function () {
        $user = User::factory()->create([
            'phone' => '1234567897',
        ]);
        $response = $this->postJson('/api/reset-password', [
            'phone' => '1234567897',
            'code' => '000000',
            'password' => 'newpassword789',
            'password_confirmation' => 'newpassword789',
        ]);
        $response->assertStatus(400)->assertJson(['message' => 'Invalid or expired code.']);
    });

    it('fails to reset password with invalid email token', function () {
        $user = User::factory()->create([
            'email' => 'resetme3@example.com',
        ]);
        $response = $this->postJson('/api/reset-password', [
            'email' => 'resetme3@example.com',
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $response->assertStatus(400)->assertJson(['message' => 'Invalid token or email.']);
    });
});
