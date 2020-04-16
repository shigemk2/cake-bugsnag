<?php

namespace Ldubois\Bugsnag\Error\Middleware;

use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware as BaseErrorHandlerMiddleware;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorHandlerMiddleware extends BaseErrorHandlerMiddleware
{

    /**
     * @return Client
     */
    public static function getBugsnag()
    {
        static $bugsnag = null;
        
        if (null === $bugsnag) {
            $bugsnag = \Bugsnag\Client::make(Configure::read('Bugsnag.apiKey'));
            $bugsnag->setBatchSending(false);
            $bugsnag->setNotifier(array(
                'name'    => Configure::read("name").' ' . Configure::read("prod") ? 'Prod' : 'Dev',
                'version' => Configure::read('version'),
                'url'     => 'http://gescomweb'
            ));

            \Bugsnag\Handler::register($bugsnag);
        }

        return $bugsnag;
    }

    
    
    
    /**
     * Handle an exception and generate an error response
     *
     * @param \Throwable $exception The exception to handle.
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        static::getBugsnag()->notifyException($exception);
        return parent::handleException($exception,$request);
    }

    
}
