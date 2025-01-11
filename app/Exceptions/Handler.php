<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report($exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */

    public function render($request, $exception)
    {
        $rendered = parent::render($request, $exception);

        if ($exception instanceof NotFoundHttpException) {
            //You can do any change as per your requrement here for the not found exception
            $statusCode = $rendered->getStatusCode();
            $message = $exception->getMessage() ? $exception->getMessage() : Response::$statusTexts[$rendered->getStatusCode()];
            $exception = new NotFoundHttpException($message, $exception);
        } elseif ($exception instanceof HttpException) {
            $statusCode = $rendered->getStatusCode();
            $message = $exception->getMessage() ? $exception->getMessage() : Response::$statusTexts[$rendered->getStatusCode()];
            $exception = new HttpException($rendered->getStatusCode(), $message);
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = env('APP_DEBUG', false) ? $exception->getMessage()." in line ". $exception->getLine() : null;
            $exception = new HttpException($statusCode, $message);
            
            if (!empty($message)) {
                return response()->json([
                    'meta' => [
                        'code' => $statusCode,
                        'message' => $exception->getMessage()
                    ]
                ], $statusCode);
            } else {
                return view(
                    'api/error/500',
                    [
                        'message' => $message
                    ]
                );
            }
        }

        return redirect('api/error/404');
    }
}
