<?php

namespace App\Entities;

use Pexess\Orm\Entity;

class Category extends Entity
{

    public int $id;
    public string $name;
    public string $created_at;
    public string $updated_at;

    protected function beforeSave(Category $category)
    {
        $category->updated_at = date("Y-m-d H:i:s");
    }

    protected string $table = 'categories';
}