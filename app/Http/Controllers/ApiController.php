<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

/**
 * Controller : ApiController
 * kontroller ini berfungsi sebagai basis bagi controller endpoint
 * API lainnya. Berisi fungsi2 basic untuk mengembalikan respon standar
 */

class ApiController extends Controller
{
    protected $statusCode = 200;    // by default it's 200 : OK
    protected $fractal;

    // PHP DEPENDENCY INJECTION!! WEIRD THING LOL
    // Secara otomatis $fractal akan terisi instance dari Manager
    public function __construct(Manager $fractal, Request $request) {

        $this->fractal = $fractal;
        // $this->request = $request;
        $this->fractal->parseIncludes($request->get('include',''));
        $this->fractal->parseExcludes($request->get('exclude',''));
    }

    // get status code
    public function getStatusCode() {
        return $this->statusCode;
    }

    // set status code, use builder pattern
    // so it can be chained
    public function setStatusCode(int $statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    // default response for single item
    public function respondWithItem($item, $transformer, array $headers = []) {
        $res = new Item($item, $transformer);           // create transformable
        $rootData = $this->fractal->createData($res);   // compose data object 

        return $this->respondWithArray($rootData->toArray(), $headers);
    }

    // default response for a collection of item
    public function respondWithCollection($collection, $transformer, array $headers = []) {
        $res = new Collection($collection, $transformer);   // create transformable

        $rootData = $this->fractal->createData($res);       // compose data object

        return $this->respondWithArray($rootData->toArray(), $headers);
    }

    // default response for a paginated items
    public function respondWithPagination($paginator, $transformer, array $headers = []) {
        $data = $paginator->getCollection();
        $res = new Collection($data, $transformer);
        $res->setPaginator(new IlluminatePaginatorAdapter($paginator));

        $rootData = $this->fractal->createData($res);

        // $this->fractal->setPaginator(new IlluminatePaginatorAdapter($paginator));
        // insert our array
        // $arr['draw'] = 1;

        return $this->respondWithArray($rootData->toArray(), $headers);
    }

    // default response for empty response (204, 200 PUT)
    public function respondWithEmptyBody(array $headers = []) {
        return response()->noContent($this->statusCode, $headers);
    }

    // default response for array (ONLY INTERNAL USAGE ALLOWED)
    public function respondWithArray(array $arr, array $headers = []) {
        // return Response::json($arr, $this->statusCode, $headers);
        return response()->json(
            $arr,
            $this->statusCode,
            $headers
        );
    }

    // default response for any kind of error
    // must supply code and message, at least
    public function respondWithError($message) {
        // prevent noob mistake
        if ($this->statusCode === 200) {
            trigger_error("MUST NOT BE ABLE TO RESPOND WITH ERROR WITH STATUS CODE 200!!!"
                , E_USER_WARNING);
        }

        // output array data in its own namespace
        return $this->respondWithArray([
            'error' => [
                'http_code' => $this->statusCode,
                'message'   => $message
            ]
        ]);
    }

    // COMMON ERRORS
    // 404 not found
    public function errorNotFound($message = "Resource not found, sorry") {
        return $this->setStatusCode(404)
                ->respondWithError($message);
    }

    // 400 bad request (user's an idiot)
    public function errorBadRequest($message = "Bad request d00d") {
        return $this->setStatusCode(400)
                ->respondWithError($message);
    }

    // 401 unauthorized (user has not supplied credentials)
    public function errorUnauthorized($message = "Credentials needed. Gib sum pls") {
        return $this->setStatusCode(401)
                ->respondWithError($message);
    }

    // 403 forbidden (requires privilege beyond user's pay grade)
    public function errorForbidden($message = "Forbidden access. Back the fuck off!") {
        return $this->setStatusCode(403)
                ->respondWithError($message);
    }

    // 405 method not allowed
    public function errorMethodNotAllowed($message = "Method not allowed, d00d") {
        return $this->setStatusCode(405)
                ->respondWithError($message);
    }

    // 500 internal server error
    public function errorInternalServer($message = "Server's having a problem. Report to administrator if you wish") {
        return $this->setStatusCode(500)
                ->respondWithError($message);
    }

    // 503 service unavailable (perhaps cause of overload or downtime)
    public function errorServiceUnavailable($message = "Service's unavailable at the moment") {
        return $this->setStatusCode(503)
                ->respondWithError($message);
    }

    // Guard this api call by returning error whenever necessary
    public function options() {
        return $this->setStatusCode(200)->respondWithEmptyBody();
    }

    /* // ensure caller has bearer token
    protected function ensureUserHasValidToken(Request $request) {
        if (!$request->bearerToken()) {
            return $this->errorUnauthorized();
            exit();
        }

        // maybe he has token, but is it valid?
        $userInfo = getUserInfo($request->bearerToken());

        if (!$userInfo) {
            return $this->errorUnauthorized("Token invalid. token = " . $request->bearerToken());
            exit();
        }

        return $userInfo;
    }

    // ensure caller has role
    protected function ensureUserHasAnyOfTheRole(Request $request, $role = '') {
        // first, ensure user has valid token
        $userInfo = $this->ensureUserHasValidToken($request);

        // role can be array or string
        if (is_string($role)) {
            // convert to array?
            $role = explode(",", $role);
            // clean up by trimming
            $role = array_map(function ($elem) {
                return trim($elem);
            }, $role);
        }

        // ok, now we compare it with actual user role
        $intersection = array_intersect($role, $userInfo['roles']);

        // do we have any of that? if not, return error
        if (count($intersection) == 0) {
            return $this->errorForbidden("You don't belong to any of these groups: " . implode($role));
        }
    } */
}
