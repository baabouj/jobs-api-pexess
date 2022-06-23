<?php

namespace App\Middlewares;

use App\Entities\User;
use Closure;
use Pexess\Auth\JWT;
use Pexess\Exceptions\ForbiddenException;
use Pexess\Exceptions\UnauthorizedException;
use Pexess\Http\Request;
use Pexess\Http\Response;
use Pexess\Middlewares\Middleware;

class Auth implements Middleware
{

    public static function role(int $role): Closure
    {
        return function (Request $req, Response $res, Closure $next) use ($role) {
            $res->throwUnless($req->user->role == $role, ForbiddenException::class);
            $next();
        };
    }

    public function handler(Request $req, Response $res, Closure $next)
    {
        $token = $this->extractJwt($req->headers()['authorization'] ?? '');
        $payload = JWT::verify($token, $_ENV['ACCESS_TOKEN_SECRET']);

        $req->user = (new User())
            ->unguard('id', 'role', 'refresh_token', 'created_at')
            ->findWhere('id', $payload['sub']);

        $next();
    }

    private function extractJwt(string $header): string
    {
        if (!str_starts_with($header, 'Bearer ')) {
            throw new UnauthorizedException();
        }

        [, $token] = explode(' ', $header);
        return $token;
    }
}