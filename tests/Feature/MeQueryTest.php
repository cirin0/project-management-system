<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('me query returns null without authentication', function (): void {
    $response = $this->postJson('/graphql', [
        'query' => '{ me { id name email } }',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.me', null);
});

test('me query returns the authenticated user when a bearer token is provided', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/graphql', [
            'query' => '{ me { id name email } }',
        ]);

    $response->assertOk();
    $response->assertJsonPath('data.me.id', (string) $user->id);
    $response->assertJsonPath('data.me.name', $user->name);
    $response->assertJsonPath('data.me.email', $user->email);
});
