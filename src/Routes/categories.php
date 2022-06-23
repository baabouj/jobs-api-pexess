<?php

use App\Constants\Roles;
use App\Controllers\CategoryController;
use App\Middlewares\Auth;
use Pexess\Router\Router;
use Pexess\Validator\Validator;

Router::controller(CategoryController::class, function () {
    Router::get('/', 'index');

    Router::get('/{category_id}', 'show');

    Router::post('/', 'store')->apply(
        Auth::class,
        Auth::role(Roles::ADMIN),
        Validator::body([
            'name' => 'required|string'
        ]));

    Router::delete('/{category_id}', 'destroy')
        ->apply(Auth::class, Auth::role(Roles::ADMIN));;
});