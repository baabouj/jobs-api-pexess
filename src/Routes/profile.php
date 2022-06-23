<?php

use App\Controllers\ProfileController;
use Pexess\Router\Router;
use Pexess\Validator\Validator;

Router::controller(ProfileController::class, function () {
//    Router::route('/')
//        ->get('show')
//        ->post('update');

    Router::get('/', 'show');

    Router::post('/', 'update')->apply(
        Validator::body([
            'resume' => 'file:pdf',
            'avatar' => 'image',
            'website' => 'url',
        ])
    );

    Router::get('/avatar', 'avatar');
});