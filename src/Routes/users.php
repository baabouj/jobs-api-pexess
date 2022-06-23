<?php

use App\Controllers\ProfileController;
use App\Controllers\UserController;
use Pexess\Router\Router;

Router::controller(UserController::class, function () {
    Router::get('/', 'index');
    Router::get('/{user_id}', 'show');
    Router::get('/{user_id}/avatar', 'avatar');
    Router::get('/{user_id}/resume', 'resume');
});

Router::get('/{user_id}/profile', [ProfileController::class,'show']);
