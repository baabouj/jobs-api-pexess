<?php

use App\Constants\Roles;
use App\Controllers\ApplicationController;
use App\Controllers\JobController;
use App\Middlewares\Auth;
use Pexess\Exceptions\NotFoundException;
use Pexess\Middlewares\Cors;
use Pexess\Router\Router;
use Pexess\Validator\Validator;

Cors::origin('http://localhost:3000');
Cors::headers('Content-Type', 'Authorization');
Cors::credentials(true);

Router::prefix('/api', function () {

    Router::prefix('/auth', function () {
        include_once 'Routes/auth.php';
    });

    Router::prefix('/users', function () {
        include_once 'Routes/users.php';
    });

    Router::prefix('/categories', function () {
        include_once 'Routes/categories.php';
    });


    Router::prefix('/jobs', function () {
//        include_once 'Routes/jobs.php';
        Router::controller(JobController::class, function () {

            Router::get('/', 'index');
            Router::get('/{job_id}', 'show');

            Router::apply(Auth::class);

            Router::post('/', 'create')
                ->apply(Auth::role(Roles::COMPANY),
                    Validator::body([
                        'position' => 'required|string',
                        'description' => 'required|string',
                        'type' => 'required|string',
                        'location' => 'required|string',
                        'experience_level' => 'required|string',
                        'salary' => 'required|number',
                        'category_id' => 'required|exist:categories,id',
                    ])
                );

            Router::route('/{job_id}')
//                ->get('show')
                ->patch('update')
                ->delete('delete')
                ->apply(
                    Validator::params([
                        'job_id' => 'exist:jobs,id'
                    ], NotFoundException::class)
                );
        });

        Router::prefix('/{job_id}/applications', function () {
            include_once 'Routes/applications.php';
        });
    });

    Router::prefix('/applications',function (){
        Router::controller(ApplicationController::class,function (){
            Router::get('/','');
        });
    });

    Router::prefix('/profile', function () {
        include_once 'Routes/profile.php';
    });

//    Router::prefix('/categories', function () {
//        include_once 'Routes/categories.php';
//    });

});