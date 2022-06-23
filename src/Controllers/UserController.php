<?php

namespace App\Controllers;

use App\Entities\Profile;
use App\Entities\User;
use Pexess\Common\Params;
use Pexess\Http\Request;
use Pexess\Http\Response;

class UserController
{
    public function __construct(private User $user, private Profile $profile)
    {
    }

    public function index(Request $req, Response $res)
    {
        $users = $this->user->findMany();

        foreach ($users as $user) {
            unset($user->password, $user->refresh_token);
        }

        $res->json($users);
    }

    public function show(Params $params, Response $res)
    {
        $this->user
            ->findWhere('id', $params->user_id);
        unset($this->user->password, $this->user->refresh_token);
        $res->json($this->user);
    }

    public function avatar(Params $params, Response $res)
    {
        $avatar = $this->profile->findWhere('user_id', $params->user_id)->avatar;

        if (!$avatar) {
            $res->noContent();
            return;
        }

        $res->type('image/*');
        readfile(dirname(__DIR__) . '/Uploads/avatars/' . $avatar);
    }

    public function resume(Params $params, Response $res)
    {
        $resume = $this->profile->findWhere('user_id', $params->user_id)->resume;

        if (!$resume) {
            $res->noContent();
            return;
        }

        $res->type('application/pdf');
        readfile(dirname(__DIR__) . '/Uploads/resumes/' . $resume);
    }

}