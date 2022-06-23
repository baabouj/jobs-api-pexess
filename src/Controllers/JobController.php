<?php

namespace App\Controllers;

use App\Entities\Job;
use Pexess\Common\Params;
use Pexess\Common\Query;
use Pexess\Exceptions\ForbiddenException;
use Pexess\Exceptions\NotFoundException;
use Pexess\Helpers\StatusCodes;
use Pexess\Http\Request;
use Pexess\Http\Response;

class JobController
{
    public function __construct(private Job $job)
    {
    }

    public function index(Query $query, Response $res)
    {
        $query->page = (int)$query->page ?? false;

        $page = (is_int($query->page) && $query->page > 0) ? $query->page : 1;

        $query->take = (int)$query->take ?? false;
        $per_page = (is_int($query->take) && $query->take > 0) ? $query->take : 9;

        $total = $this->job->count([
            'where' => [
                'published' => true
            ],
        ]);

        $jobs = $this->job->findMany([
            'where' => [
                'published' => true
            ],
            'take' => $per_page,
            'skip' => ($page - 1) * $per_page
        ]);

        $last_page = ceil($total / $per_page);
        $next_page = $page + 1 > $last_page ? null : $page + 1;
        $prev_page = $page - 1 <= 0 ? null : $page - 1;

        $res->json([
            "info" => [
                "total" => $total,
                "current_page" => $page,
                "next_page" => $next_page,
                "prev_page" => $prev_page,
                "last_page" => $last_page,
                "per_page" => $per_page,
            ],
            "data" => $jobs
        ]);
    }

    public function show(Params $params, Response $res)
    {
        $job = $this->job
            ->findUnique([
                'where' => [
                    'id' => $params->job_id,
                    'published' => true
                ]
            ]);

        $res->throwIf(!$job, NotFoundException::class);

        unset($job->published);

        $res->json($job);
    }

    public function create(Request $req, Response $res)
    {
        $this->job
            ->guard('id', 'company_id', 'published', 'created_at', 'updated_at')
            ->fill($req->body())
            ->unguard('id', 'published', 'created_at')
            ->company_id = $req->user->id;

        $this->job->save();

        $res
            ->status(StatusCodes::CREATED)
            ->json($this->job);
    }

    public function update(Request $req, Response $res)
    {
        $this->job->findWhere('id', $req->params()['job_id']);

        $res->throwUnless($req->user->id == $this->job->company_id, ForbiddenException::class);

        $this->job
            ->guard('id', 'company_id', 'category_id', 'created_at', 'updated_at')
            ->fill($req->body())
            ->save();

        $res->json($this->job);
    }

    public function delete(Request $req, Response $res)
    {
        $this->job->findWhere('id', $req->params()['job_id']);

        $res->throwUnless($req->user->id == $this->job->company_id, ForbiddenException::class);

        $this->job->destroy();

        $res->noContent();
    }
}