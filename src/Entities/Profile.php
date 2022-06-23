<?php

namespace App\Entities;

use Pexess\Orm\Entity;

class Profile extends Entity
{
    public int $id;
    public int $user_id;
    public string $bio;
    public string $avatar;
    public string $resume;
    public string $website;
    public string $address;
    public string $created_at;
    public string $updated_at;

    protected function beforeSave(Profile $profile)
    {
        $profile->updated_at = date("Y-m-d H:i:s");
    }

    protected string $table = 'profiles';
}