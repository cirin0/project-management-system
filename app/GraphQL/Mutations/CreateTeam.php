<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Team;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

final readonly class CreateTeam
{
    /** @param array{} $args */
    public function __invoke(null $_, array $args): Team
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $data = $args['input'];
        $data['owner_id'] = $user->id;

        return Team::query()->create($data);
    }
}
