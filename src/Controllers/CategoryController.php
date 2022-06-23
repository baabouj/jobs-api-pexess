<?php

namespace App\Controllers;

use App\Entities\Category;
use Pexess\Common\Body;
use Pexess\Common\Params;
use Pexess\Http\Response;

class CategoryController
{
    public function __construct(private Category $category)
    {
    }

    public function index(Response $res)
    {
        $res->json($this->category->findMany());
    }

    public function show(Params $params, Response $res)
    {
        $this->category->findWhere('id', $params->category_id);
        $res->json($this->category);
    }

    public function store(Body $body, Response $res)
    {
        $this->category->name = $body->name;
        $this->category->save();
        $res->json($this->category);
    }

    public function destroy(Params $params, Response $res)
    {
        $this->category->findWhere('id', $params->category_id);
        $this->category->destroy();
        $res->noContent();
    }

}