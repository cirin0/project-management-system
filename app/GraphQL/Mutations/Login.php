<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Nuwave\Lighthouse\Exceptions\DefinitionException;

final readonly class Login
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $data = $args['input'];

        $user = User::where('email', $data['email'])->first();

        if (!$user || !password_verify($data['password'], $user->password)) {
            throw new DefinitionException('Invalid credentials');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
        ];
    }
}
