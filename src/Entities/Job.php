<?php

namespace App\Entities;

use Pexess\Orm\Entity;

class Job extends Entity
{
    public int $id;
    public string $position;
    public string $description;
    public string $type;
    public string $location;
    public string $experience_level;
    public float $salary;
    public bool $published;
    public int $company_id;
    public int $category_id;
    public string $created_at;
    public string $updated_at;

    protected function beforeSave(Job $job)
    {
        $job->validate();
        $job->updated_at = date("Y-m-d H:i:s");
    }

    protected function rules(): array
    {
        return [
            'position' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|string',
            'experience_level' => 'required|string',
            'salary' => 'required|number',
        ];
    }

    protected string $table = 'jobs';
//    protected array $guard = ['id', 'company_id', 'category_id', 'published', 'created_at', 'updated_at'];
}