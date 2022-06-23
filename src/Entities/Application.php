<?php

namespace App\Entities;

use Pexess\Orm\Entity;

class Application extends Entity
{
    public int $id;
    public int $job_id;
    public int $user_id;
    public string $status;
    public string $created_at;
    public string $updated_at;

    protected function beforeSave(Application $application)
    {
        $application->updated_at = date("Y-m-d H:i:s");
    }

    protected string $table = 'applications';
}