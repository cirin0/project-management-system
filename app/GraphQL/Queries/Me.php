<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final readonly class Me
{
    public function __invoke(mixed $_, array $args): ?User
    {
        return Auth::user();
    }
}
