<?php

use App\Constants\Roles;
use App\Controllers\ApplicationController;
use App\Middlewares\Auth;
use Pexess\Exceptions\NotFoundException;
use Pexess\Router\Router;
use Pexess\Validator\Validator;

Router::controller(ApplicationController::class, function () {
    Router::get('/', 'index')->apply(Auth::role(Roles::COMPANY));

    Router::post('/', 'store')
        ->apply(
            Auth::role(Roles::USER),
            Validator::params([
                'job_id' => 'exist:jobs,id'
            ], NotFoundException::class)
        );

    Router::get('/{application_id}', 'show')->apply(Auth::role(Roles::USER));

    Router::delete('/{application_id}', 'destroy')->apply(Auth::role(Roles::USER));
});