<?php

use App\Controllers\AuthController;
use App\Middlewares\Auth;
use Pexess\Router\Router;
use Pexess\Validator\Validator;

Router::controller(AuthController::class, function () {
    Router::post('/login', 'login')
        ->apply(Validator::body([
//                'email' => 'required|email|exist:users',
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]
        ));

    Router::post('/signup', 'signup')
        ->apply(Validator::body([
                'name' => 'required|string',
//                'email' => 'required|email|unique:users',
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]
        ));

    Router::post('/refresh', 'refresh');

    Router::post('/logout', 'logout')->apply(Auth::class);

    Router::get('/me', 'me')->apply(Auth::class);
});