<?php

namespace App\Entities;

use Pexess\Exceptions\BadRequestException;
use Pexess\Helpers\Hash;
use Pexess\Orm\Entity;

class User extends Entity
{
    public int $id;
    public string $name;
    public int $role;
    public string $email;
    public string $password;
    public string $refresh_token;

    protected function beforeSave(User $user)
    {
        $this->validate();
        $isHashed = password_get_info($this->password)["algo"] == "argon2i";
        if (!$isHashed) $user->password = Hash::hash($user->password, PASSWORD_ARGON2I);
    }

    protected function afterSave(User $user)
    {
        unset($user->password);
    }

    protected function fallback()
    {
        throw new BadRequestException();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }

    protected string $table = 'users';
//    protected array $guard = ['id', 'role', 'refresh_token', 'created_at'];
}