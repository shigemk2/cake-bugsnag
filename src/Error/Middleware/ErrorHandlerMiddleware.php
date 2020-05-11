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
    public static function getBugsnag($request)
    {
        static $bugsnag = null;

        if (null === $bugsnag) {
            $bugsnag = \Bugsnag\Client::make(Configure::read('Bugsnag.apiKey'));
            $bugsnag->setBatchSending(false);
            $bugsnag->setNotifier(array(
                'name'    => Configure::read("name") . ' ' . Configure::read("prod") ? 'Prod' : 'Dev',
                'version' => Configure::read('tag') ?? Configure::read('version'),
                'url'     => 'http://gescomweb',
            ));


            $bugsnag->setAppVersion(Configure::read('tag') ?? Configure::read('version'));
            $bugsnag->setReleaseStage(Configure::read("prod") ? 'production' : 'development');
            $bugsnag->setAppType('CakePhP');
            session_start();
            $bugsnag->registerCallback(function ($report) {
                $confUser = Configure::read('Bugsnag.userId');
                $user = array();
                if (!empty($confUser)) {
                    $tabUser = explode('.', $confUser);

                    foreach ($tabUser as $tab) {
                        if (empty($user['id'])) {
                            if (isset($_SESSION[$tab])) {
                                $user['id'] = $_SESSION[$tab];
                            }
                        } else {
                            if (isset($user['id'][$tab])) {
                                $user['id'] = $user['id'][$tab];
                            }
                        }
                    }

                    $confUser = Configure::read('Bugsnag.userName');
                    if (!empty($confUser)) {
                        $tabUser = explode('.', $confUser);

                        foreach ($tabUser as $tab) {
                            if (empty($user['name'])) {
                                if (isset($_SESSION[$tab])) {
                                    $user['name'] = $_SESSION[$tab];
                                }
                            } else {
                                if (isset($user['name'][$tab])) {
                                    $user['name'] = $user['name'][$tab];
                                }
                            }
                        }
                    }
                } else {
                    $user = ["id" => $_SESSION['Auth']['User']['id'], "name" => $_SESSION['Auth']['User']['name']];
                }
                $report->setUser($user);
            });

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
        static::getBugsnag($request)->notifyException($exception);
        return parent::handleException($exception, $request);
    }
}
