<?php

namespace Ldubois\Bugsnag;

use Cake\Core\BasePlugin;
use Cake\Error\Middleware\ErrorHandlerMiddleware as CakeErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Ldubois\Bugsnag\Error\Middleware\ErrorHandlerMiddleware;

class Plugin extends BasePlugin
{
    /**
     * {@inheritDoc}
     */
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        $middleware = parent::middleware($middleware);
        $middleware->insertAfter(
            CakeErrorHandlerMiddleware::class,
            new ErrorHandlerMiddleware()
        );

        return $middleware;
    }
}
