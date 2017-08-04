<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        // return parent::render($request, $e);

        $responseData = [
            'error'         => true,
            'message'       => (string) $e->getMessage(),
            'result'        => null
        ];

        $statusCode = 400;


        if ($e instanceof HttpException) {
            $responseData['message'] = Response::$statusTexts[$e->getStatusCode()];
            $statusCode = $e->getStatusCode();
        } else if ($e instanceof ModelNotFoundException) {
            $responseData['message'] = Response::$statusTexts[Response::HTTP_NOT_FOUND];
            $statusCode = Response::HTTP_NOT_FOUND;
        }

        if ($this->isDebugMode()) {
            $responseData['debug'] = [
                'exception' => get_class($e),
                'trace' => $e->getTrace()
            ];
        }

        return response()->json($responseData, $statusCode);

    }

    /**
     * Determine if the application is in debug mode.
     *
     * @return Boolean
     */
    public function isDebugMode()
    {
        return (boolean) env('APP_DEBUG');
    }

}
