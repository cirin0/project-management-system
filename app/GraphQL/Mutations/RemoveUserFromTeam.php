<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Team;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

final readonly class RemoveUserFromTeam
{
    /** @param array{} $args */
    public function __invoke(null $_, array $args): Team
    {
        $authUser = Auth::user();
        if (!$authUser) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $team = Team::query()->findOrFail($args['teamId']);

        if ($team->owner_id !== $authUser->id) {
            throw new AuthorizationException('Only the team owner can remove members.');
        }

        $userId = $args['userId'];

        $team->users()->detach($userId);

        return $team->fresh();
    }
}
