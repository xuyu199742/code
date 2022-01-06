<?php

namespace App\Providers;

use App\Exceptions\NewException;
use App\Http\Requests\ClientRequest;
use Dingo\Api\Http\RequestValidator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->extend(RequestValidator::class, function ($service) {
            $service->merge([ClientRequest::class]);
            return $service;
        });
        app('api.exception')->register(function (\Exception $exception) {

            if (\Request::is("api/client/*") || \Request::is("api/payment/*")) {
                if ($exception instanceof ValidationException) {
                    $message = $exception->validator->getMessageBag()->getMessages();
                    if (is_array($message)) {
                        $message = array_values($message);
                        $message = $message[0][0];
                    }
                    return ResponeFails($message);
                }
            }
            if($exception instanceof NewException) {
                return ResponeFails($exception->getMessage());
            }

            if ($exception instanceof ValidationException) {
                $message = $exception->validator->getMessageBag()->getMessages();
                if (is_array($message)) {
                    $message = array_values($message);
                    $message = $message[0][0];
                }
                return ResponeFails($message, [], 200);
            }

            if ($exception instanceof AuthenticationException) {
                return ResponeFails('登录失败', [$exception->getMessage() ?? ''], 401);
            }
            if ($exception instanceof NotFoundHttpException || $exception instanceof HttpException) {
                return ResponeFails('无效请求', [$exception->getMessage() ?? ''], 404);
            }

            if($exception instanceof  AuthorizationException ) {
                return ResponeFails($exception->getMessage() ?? '', [], 200);
            }

            return ResponeFails('系统异常', [$exception->getMessage() ?? ''], 200);
        });

    }
}
