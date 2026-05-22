<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create a team', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/graphql', [
            'query' => <<<'GQL'
                mutation {
                    createTeam(input: {
                        name: "Test Team"
                        description: "A test team"
                    }) {
                        id
                        name
                        description
                    }
                }
            GQL,
        ]);

    $response->assertOk();
    $response->assertJsonPath('data.createTeam.name', 'Test Team');
    $response->assertJsonPath('data.createTeam.description', 'A test team');

    expect(Team::where('name', 'Test Team')->exists())->toBeTrue();
});

test('team owner can add user to team', function (): void {
    $owner = User::factory()->create();
    $userToAdd = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    $token = $owner->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/graphql', [
            'query' => sprintf(<<<'GQL'
                mutation {
                    addUserToTeam(teamId: %d, userId: %d) {
                        id
                    }
                }
            GQL, $team->id, $userToAdd->id),
        ]);

    $response->assertOk();
    $response->assertJsonPath('data.addUserToTeam.id', (string) $team->id);

    expect($team->users()->where('user_id', $userToAdd->id)->exists())->toBeTrue();
});

test('non-owner cannot add user to team', function (): void {
    $owner = User::factory()->create();
    $notOwner = User::factory()->create();
    $userToAdd = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    $token = $notOwner->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/graphql', [
            'query' => sprintf(<<<'GQL'
                mutation {
                    addUserToTeam(teamId: %d, userId: %d) {
                        id
                    }
                }
            GQL, $team->id, $userToAdd->id),
        ]);

    $response->assertOk();
    expect($response->json('errors'))->not->toBeNull();
});

test('team owner can remove user from team', function (): void {
    $owner = User::factory()->create();
    $userInTeam = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->users()->attach($userInTeam->id, ['role' => 'member']);

    $token = $owner->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/graphql', [
            'query' => sprintf(<<<'GQL'
                mutation {
                    removeUserFromTeam(teamId: %d, userId: %d) {
                        id
                    }
                }
            GQL, $team->id, $userInTeam->id),
        ]);

    $response->assertOk();
    expect($team->users()->where('user_id', $userInTeam->id)->exists())->toBeFalse();
});
