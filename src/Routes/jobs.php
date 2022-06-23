<?php

use App\Constants\Roles;
use App\Controllers\ApplicationController;
use App\Controllers\JobController;
use App\Middlewares\Auth;
use Pexess\Exceptions\NotFoundException;
use Pexess\Router\Router;
use Pexess\Validator\Validator;

Router::controller(JobController::class, function () {

    Router::get('/', 'index');

    Router::post('/', 'create')
        ->apply(Auth::role(Roles::COMPANY),
            Validator::body([
                'position' => 'required|string',
                'description' => 'required|string',
                'type' => 'required|string',
                'experience_level' => 'required|string',
                'salary' => 'required|number',
                'category_id' => 'required|number|exist:categories,id',
            ])
        );

    Router::route('/{job_id}')
        ->get('show')
        ->patch('update')
        ->delete('delete')
        ->apply(
            Validator::params([
                'job_id' => 'exist:jobs,id'
            ], NotFoundException::class)
        );
});

Router::post('/{job_id}/apply', [ApplicationController::class, 'store'])
    ->apply(
        Auth::role(Roles::USER),
        Validator::params([
            'job_id' => 'exist:jobs,id'
        ], NotFoundException::class)
    );
