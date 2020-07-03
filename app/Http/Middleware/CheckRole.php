<?php

namespace App\Http\Middleware;

use League\Fractal;
use App\Http\Controllers\ApiController;

use Closure;

class CheckRole
{
    /**
     * Ni fungsi buat ngeguard api, memastikan user pake token / punya role yg sesuai
     * klo apinya open, jgn dipake.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roleNames)
    {
        // ensure user has valid token first. only then it makes sense
        // to check role
        $token = $request->bearerToken();
        $api = new ApiController(new Fractal\Manager(), $request);

        if (!$token)
            return $api->errorUnauthorized();

        // okay, there's token. but is it valid?
        $userInfo = getUserInfo();

        if (!$userInfo)
            return $api->errorUnauthorized("Token invalid. Token used: " . $token);
        
        // maybe user is registered but is not activated?
        $active = strtoupper($userInfo['status'] ?? false);

        if (!$active)
            return $api->errorUnauthorized("User with this token is not active. Kys, fag");
        
        // okay, token is valid and user is active. but is the user in the correct role/group?
        // if the middleware is not supported any arguments, that means
        // it will pass the test if user is valid and active

        // if at least one role is specified, test for that
        if (count($roleNames) > 0) {
            // perhaps this user has no role in that app
            if (!array_key_exists('11', $userInfo['apps_data'])) {
                // he has no role what so ever..but he is required to have
                return $api->errorForbidden("You have no role in this application (TePePe). Contact Administrator for further assistance!");
            }

            $userRoles = $userInfo['apps_data']['11']['roles'] ?? [];

            $matchingRoles = array_intersect($userRoles, $roleNames);

            // no matching roles
            if (count($matchingRoles) == 0) {
                // just for now, ensure invalid
                return $api->errorForbidden("You do not belong to any of this group: " . implode(', ', $roleNames));   
            }
        }

        // store user info on a request?
        // $request->merge(['sso_user' => $userInfo]);
        $request->userInfo = $userInfo;

        // pass role check. continue
        return $next($request);
    }
}
