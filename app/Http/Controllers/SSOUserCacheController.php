<?php

namespace App\Http\Controllers;

use App\Services\SSO;
use App\SSOUserCache;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class SSOUserCacheController extends ApiController
{
    // our ctor
    public function __construct(SSO $sso, Manager $fractal, Request $r)
    {
        parent::__construct($fractal, $r);

        $this->sso = $sso;
    }


    public function index(Request $r) {
        // just return by roles if possible
        $roles = $r->get('role') ?? $r->get('roles');

        if (!$roles) {
            return $this->errorBadRequest("At least specify the roles to query...");
        }

        try {
            // split them into arrays
            $arr_roles = explode(",", $roles);
    
            $users = $this->sso->getUserByRole($arr_roles, true);
    
            return $this->respondWithArray($users);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function show(Request $r, $id) {
        if (!$id) {
            return $this->errorBadRequest("User id is not specified");
        }

        try {
            //code...
            $user = SSOUserCache::byId($id);

            return $this->respondWithArray($user->toArray());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
