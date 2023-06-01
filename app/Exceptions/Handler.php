<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                   'status' => false,
                   'message' => 'Anime not found',
              ], 404);
        });

        $this->renderable(function (MethodNotAllowedHttpException $e) {
            return response()->json([
                   'status' => false,
                   'message' => 'Bad http method request',
              ], 400);
        });
        
    }
}
