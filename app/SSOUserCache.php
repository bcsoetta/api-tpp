<?php

namespace App;

use App\Services\SSO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class SSOUserCache extends Model
{
    // table setting
    protected $table = 'sso_user_cache';

    // column setting
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'user_id';

    // static method? to automatically grab and cache
    // cache user data
    static public function cacheUserData($data) {
        $user = SSOUserCache::updateOrCreate($data);

        return $user;
    }

    // get user data?
    // FROM SERVER
    static public function getUserFromServer($id) {
        
        try {
            // grab sso broker instance
            $sso = app(SSO::class);

            // call method
            $data = $sso->getUserById($id);

            return $data['data'][0];
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'request'   => [
                    'user_id'   => $id
                ]
            ]);

            return null;
        }
    }

    // FROM DB
    static public function getUserFromCache($id) {
        try {
            $user = SSOUserCache::findOrFail($id);
            return $user;
        } catch (\Exception $th) {
            return null;
        }
    }

    // FROM CACHE, AUTO FETCH WHEN NOT EXIST
    static public function byId($id, $forceFetch = false) {
        // 1st, grab from cache
        $user = SSOUserCache::getUserFromCache($id);

        // 2nd, if it does not exist, grab from server
        // NB: ALSO DO IT IF WE'RE FORCE FETCHING
        if (!$user || $forceFetch) {
            $user = SSOUserCache::getUserFromServer($id);

            // 3rd, if we got one, cache it
            if ($user) {
                // here we convert what we got from server into eloquent model
                $user = SSOUserCache::cacheUserData($user);
            }
        }

        // throw exception if we can't find it
        if (!$user) {
            throw new ModelNotFoundException("SSO User #{$id} was not found");
        }

        // return whatever we got (or not)
        return $user;
    }
}
