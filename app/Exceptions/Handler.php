<?php

namespace App\Exceptions;

use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use League\Fractal\Manager;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        $c = new ApiController(new Manager(), $request);

        // kalo exception timbul karena maintenance mode aktif
        if ($exception instanceof MaintenanceModeException) {
            // spawn our REST api controller to handle this

            // special headers to handle CORS compatibility
            $headers = [
                'Access-Control-Allow-Origin' => "*",
                'Access-Control-Allow-Methods' => $request->method(),
                'Access-Control-Allow-Headers' => 'Authorization,Content-Type,Content-Length,X-Content-Filesize,X-Content-Type,X-Content-Filename'
            ];

            // build message
            $message = $exception->getMessage();
            if (strlen($message) < 1) {
                $message = "Maintenance mode started";
            } 
            // decorate it
            $message = "👋👋👋 👉MAINTENANCE_MESSAGE [{$message}]👈";
            $message .= ". Started @ " . $exception->wentDownAt;
            if ($exception->willBeAvailableAt) {
                $message .= ", Will be up again probably around " . $exception->willBeAvailableAt;
            }
            

            // if it's OPTIONS request, let it pass
            if ($request->method() == "OPTIONS") {
                return $c->options()->withHeaders($headers);
            }

            // on the real request, respond accordingly
            return $c->errorServiceUnavailable($message)
                ->withHeaders($headers);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $c->errorMethodNotAllowed("Invalid method invokation. You're not hacking me ain't ye?");
        }

        return parent::render($request, $exception);
    }
}
