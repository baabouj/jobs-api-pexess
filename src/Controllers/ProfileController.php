<?php

namespace App\Controllers;

use App\Constants\Roles;
use App\Entities\Profile;
use App\Entities\User;
use Pexess\Common\Params;
use Pexess\Http\Request;
use Pexess\Http\Response;

class ProfileController
{
    public function __construct(private Profile $profile)
    {
    }

    public function avatar(Request $req, Response $res)
    {
        $res->type('image/*');
        $avatar = $this->profile->findWhere('user_id', $req->user->id)->avatar;
        readfile(dirname(__DIR__) . '/Uploads/avatars/' . $avatar);
    }

    public function show(Params $params, Response $res)
    {
        $user = (new User())->unguard('id', 'role', 'refresh_token', 'created_at')->findWhere('id', $params->user_id);
        $this->profile->findWhere('user_id', $params->user_id);

        $this->profile->name = $user->name;
        $this->profile->email = $user->email;

        if ($user->role == Roles::COMPANY) {
            unset($this->profile->resume);
        }

        if ($user->role == Roles::USER) {
            unset($this->profile->website, $this->profile->address);
        }

        unset($this->profile->user_id, $this->profile->id);
        $res->json($this->profile);
    }

    public function update(Request $req, Response $res)
    {
        $body = $req->body();
        $guards = ['id', 'user_id', 'created_at', 'updated_at'];

        $this->profile->findWhere('user_id', $req->user->id);

        if ($req->user->role == Roles::USER) {
            array_push($guards, 'website', 'address');

            if ($req->file('resume')) {
                $resume = $this->uploadFile($req->file('resume'), "resumes");
                $this->deleteFile($this->profile->resume, 'resumes');
                $this->profile->resume = $resume;
            }
        }

        if ($req->user->role == Roles::COMPANY) {
            $guards[] = 'resume';
        }

        if ($req->file('avatar')) {
            $avatar = $this->uploadFile($req->file('avatar'), "avatars");
            $this->deleteFile($this->profile->avatar, 'avatars');
            $this->profile->avatar = $avatar;
        }

        $this->profile
            ->guard(...$guards)
            ->fill($body)
            ->save();

        if ($req->user->role == Roles::COMPANY) {
            unset($this->profile->resume);
        }

        if ($req->user->role == Roles::USER) {
            unset($this->profile->website, $this->profile->address);
        }

        unset($this->profile->user_id, $this->profile->id);
        $res->json($this->profile);
    }

    private function uploadFile($file, string $dir): string
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = time() . '.' . $extension;
        move_uploaded_file($file['tmp_name'], dirname(__DIR__) . '/Uploads/' . $dir . '/' . $new_name);
        return $new_name;
    }

    private function deleteFile(string $file, string $dir)
    {
        unlink(dirname(__DIR__) . '/Uploads/' . $dir . '/' . $file);
    }

}