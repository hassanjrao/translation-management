<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function issueToken(User $user, ?string $name = null): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $prefix = Str::substr($plainToken, 0, 12);

        ApiToken::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'token_prefix' => $prefix,
            'token_hash' => Hash::make($plainToken),
        ]);

        return $plainToken;
    }

    public function validateToken(string $plainToken): ?User
    {
        $prefix = Str::substr($plainToken, 0, 12);
        $tokens = ApiToken::query()
            ->where('token_prefix', $prefix)
            ->with('user')
            ->get();

        foreach ($tokens as $token) {
            if (Hash::check($plainToken, $token->token_hash)) {
                $token->forceFill(['last_used_at' => CarbonImmutable::now()])->save();
                return $token->user;
            }
        }

        return null;
    }

    public function revokeToken(string $plainToken): void
    {
        $prefix = Str::substr($plainToken, 0, 12);
        $tokens = ApiToken::query()->where('token_prefix', $prefix)->get();

        foreach ($tokens as $token) {
            if (Hash::check($plainToken, $token->token_hash)) {
                $token->delete();
            }
        }
    }
}

