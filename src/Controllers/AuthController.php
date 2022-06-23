<?php

namespace App\Controllers;

use App\Constants\Roles;
use App\Entities\User;
use Pexess\Auth\JWT;
use Pexess\Common\Body;
use Pexess\Database\Database;
use Pexess\Exceptions\BadRequestException;
use Pexess\Exceptions\ForbiddenException;
use Pexess\Exceptions\UnauthorizedException;
use Pexess\Helpers\Hash;
use Pexess\Helpers\StatusCodes;
use Pexess\Http\Request;
use Pexess\Http\Response;

class AuthController
{
    public function __construct(private User $user)
    {
    }

    public function login(Request $req, Response $res)
    {
        $body = $req->body();
        $this->user
            ->findWhere('email', $body['email']);

        $login_attempts = Database::from('login_attempts')->count([
            'where' => [
                "user_id" => $this->user->id,
                "timestamp" => [
                    "gte" => time() - 60 * 60
                ]
            ]
        ]);

        $res->throwIf((int)$_ENV["MAX_LOGIN_ATTEMPTS_PER_HOUR"] <= $login_attempts, BadRequestException::class);

        $isMatched = Hash::compare($body['password'], $this->user->password);

        if (!$isMatched) {
            Database::from('login_attempts')->create([
                    "data" => [
                        "user_id" => $this->user->id,
                        "timestamp" => time()
                    ]
                ]
            );

            $res->throw(BadRequestException::class);
        }

        $res->throwUnless($isMatched, BadRequestException::class);

        $access_token = JWT::generate([
            'sub' => $this->user->id,
            'role' => $this->user->role,
        ], $_ENV['ACCESS_TOKEN_SECRET'], [
            'expires' => time() + 10
        ]);

        $refresh_token = JWT::generate([
            'sub' => $this->user->id,
        ], $_ENV['REFRESH_TOKEN_SECRET'], [
            'expires' => time() + 60 * 60 * 24
        ]);

        if ($req->cookie('token')) {
//            var_dump($req->cookie('token'));
            $res->clearCookie('token', [
                'httponly' => true,
                'secure' => true,
                'samesite' => 'None',
            ]);
        }

        $this->user->refresh_token = $refresh_token;

        $this->user->save();

        $res->cookie('token', $refresh_token, [
            'httponly' => true,
            'secure' => true,
            'samesite' => 'None',
        ]);

        $res->json([
            'access_token' => $access_token,
            'role' => $this->user->role
        ]);
    }

    public function signup(Body $body, Response $res)
    {
        $res->throwIf($this->user->count([
            'where' => [
                'email' => $body->email
            ]
        ]), BadRequestException::class);

        $this->user
            ->guard('id', 'role', 'refresh_token', 'created_at')
            ->fill($body)
            ->role = Roles::USER;

        $this->user
            ->unguard('id', 'role', 'refresh_token', 'created_at')
            ->save();

        Database::from('profiles')->create([
            'data' => [
                'user_id' => $this->user->id
            ]
        ]);

        $access_token = JWT::generate([
            'sub' => $this->user->id,
            'role' => $this->user->role,
        ], $_ENV['ACCESS_TOKEN_SECRET'], [
            'expires' => time() + 10
        ]);

        $refresh_token = JWT::generate([
            'sub' => $this->user->id,
        ], $_ENV['REFRESH_TOKEN_SECRET'], [
            'expires' => time() + 60 * 60 * 24
        ]);

        $res->cookie('token', $refresh_token, [
            'httponly' => true,
            'secure' => true,
            'samesite' => 'None',
        ]);

        $res->status(StatusCodes::CREATED)
            ->json([
                'access_token' => $access_token,
                'role' => $this->user->role,
            ]);
    }

    public function refresh(Request $req, Response $res)
    {
        $token = $req->cookie('token');

        $res->throwIf(!$token, ForbiddenException::class);

        $res->clearCookie('token', [
            'httponly' => true,
            'secure' => true,
            'samesite' => 'None',
        ]);

        $user = $this->user->findUnique([
            'where' => [
                'refresh_token' => $token
            ]
        ]);


        if (!$user) {
            $payload = JWT::verify($token, $_ENV['REFRESH_TOKEN_SECRET']);

            $hackedUser = $this->user->findUnique([
                'where' => [
                    'id' => $payload['sub']
                ]
            ]);

            $hackedUser->refresh_token = '';

            $hackedUser->save();

            $res->throw(UnauthorizedException::class);
        }


        try {
            $payload = JWT::verify($token, $_ENV['REFRESH_TOKEN_SECRET']);
        } catch (\Exception $_) {
            $user->refresh_token = '';
            $user->save();
            $res->throw(UnauthorizedException::class);
        }

        $res->throwIf($user->id !== $payload['sub'], UnauthorizedException::class);

        $access_token = JWT::generate([
            'sub' => $user->id,
            'email' => $user->email,
        ], $_ENV['ACCESS_TOKEN_SECRET'], [
            'expires' => time() + 10
        ]);

        $refresh_token = JWT::generate([
            'sub' => $user->id,
        ], $_ENV['REFRESH_TOKEN_SECRET'], [
            'expires' => time() + 60 * 60 * 24
        ]);

        $user->refresh_token = $refresh_token;

        $user->save();

        $res->cookie('token', $refresh_token, [
            'httponly' => true,
            'secure' => true,
            'samesite' => 'None',
        ]);

        $res->json([
            'access_token' => $access_token,
            'role' => $user->role,
        ]);
    }

    public function logout(Request $req, Response $res)
    {
        $token = $req->cookie('token');

        if (!$token)
            $res->status(StatusCodes::NO_CONTENT)->end();


        $res->clearCookie('token', [
            'httponly' => true,
            'secure' => true,
            'samesite' => 'None',
        ]);

        $req->user->refresh_token = '';
        $req->user->save();

        $res->noContent();
    }

    public function me(Request $req, Response $res)
    {
        unset($req->user->password, $req->user->refresh_token);
        $res->json($req->user);
    }
}