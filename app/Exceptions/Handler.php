<?php

namespace App\Exceptions;

use App\Components\OutputUtil;
use App\Components\PFException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $info = parent::render($request, $exception);
        pflog('error', $exception->getMessage());
        if ($_SERVER['SERVER_NAME'] == DOMAIN_APP) {
            /* 错误页面 */
            if ($exception instanceof NotFoundHttpException) {
                $code = $exception->getStatusCode();
                OutputUtil::err(ERR_SYS_UNKNOWN_CONTENT, $code);
            } elseif ($exception instanceof PFException) {
                OutputUtil::err($exception->getMessage(), $exception->getCode());
            } else {
                OutputUtil::err(ERR_SYS_UNKNOWN_CONTENT, ERR_SYS_UNKNOWN);
            }
        } else {
            return $info;
        }
    }
}
