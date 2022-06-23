<?php

namespace App\Controllers;

use App\Constants\ApplicationStatus;
use App\Entities\Application;
use Pexess\Exceptions\BadRequestException;
use Pexess\Exceptions\ForbiddenException;
use Pexess\Http\Request;
use Pexess\Http\Response;

class ApplicationController
{
    public function __construct(private Application $application)
    {
    }

    public function index(Request $req, Response $res)
    {
        $applications = $this->application->findMany([
            'where' => [
                'user_id' => $req->user->id,
                'job_id' => $req->params()['job_id'],
            ]
        ]);

        $res->json($applications);
    }

    public function show(Request $req, Response $res)
    {
        $this->application->findWhere('id', $req->params()['application_id']);

        $res->json($this->application);
    }

    public function store(Request $req, Response $res)
    {

        $res->throwUnless(!$this->application->count([
            'where'=>[
                'user_id' => $req->user->id,
                'job_id' => $req->params()['job_id'],
            ]
        ]),BadRequestException::class);

        $this->application
            ->fill([
                'user_id' => $req->user->id,
                'job_id' => $req->params()['job_id'],
                'status' => ApplicationStatus::PENDING,
            ])
            ->save();

        $res->json($this->application);
    }

    public function destroy(Request $req, Response $res)
    {
        $this->application->findWhere('id', $req->params()['application_id']);

        $res->throwUnless($this->application->user_id == $req->user->id, ForbiddenException::class);

        $this->application->destroy();

        $res->noContent();
    }
}