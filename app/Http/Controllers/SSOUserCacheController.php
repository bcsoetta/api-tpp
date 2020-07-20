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
    
            $data = $this->sso->getUserByRole($arr_roles, true);

            // filter it?
            $q = $r->get('q');

            if ($q && strlen(trim($q)) > 0) {
                // refine by name?
                $data['data'] = array_values(array_filter($data['data'], function ($e) use ($q) {
                    $pattern = "/$q/i";

                    return preg_match($pattern, $e['name']) || preg_match($pattern, $e['nip']);
                }));

                // remove key?
                
            }
    
            return $this->respondWithArray($data);
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

    public function getKasi(Request $r) {
        try {
            // grab all user who has role pemeriksa
            $data = $this->sso->getUserByRole(['tpp.kasi'], false);

            // modify it a bit
            $q = $r->get('q');

            if ($q && strlen(trim($q)) > 0) {
                // refine by name?
                $data['data'] = array_values(array_filter($data['data'], function ($e) use ($q) {
                    $pattern = "/$q/i";

                    return preg_match($pattern, $e['name']) || preg_match($pattern, $e['nip']);
                }));

                // remove key?
                
            }

            return $this->respondWithArray($data);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage() . ' Code: ' . $e->getCode());
        }
    }
}
