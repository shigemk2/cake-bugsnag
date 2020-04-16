<?php

namespace Ldubois\Bugsnag\Http;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Error\PHP7ErrorException;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Utility\Hash;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;


use \Bugsnag\Client as ClienBugsnag;


class Client
{
    use EventDispatcherTrait;
    use InstanceConfigTrait;

    /* @var array default instance config */
    protected $_defaultConfig = [];


    /* @var ServerRequestInterface */
    protected $request;

    /**
     * Client constructor.
     *
     * @param array $config config for uses Bugsnag
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
        $this->setupClient();
    }

    /**
     * Accessor for current hub
     * @return ClienBugsnag
     */
    public function getClient(): ClienBugsnag
    {
        static $bugsnag = null;
        
        if (null === $bugsnag) {
            $bugsnag = \Bugsnag\Client::make(Configure::read('Bugsnag.apiKey'));
            $bugsnag->setBatchSending(false);
            $bugsnag->setNotifier(array(
                'name'    => Configure::read("name").' ' . Configure::read("prod") ? 'Prod' : 'Dev',
                'version' => Configure::read('version'),
                
            ));

            \Bugsnag\Handler::register($bugsnag);
        }

        return $bugsnag;
    }

    /**
     * Capture exception for bugsnag.
     *
     * @param mixed $level error level
     * @param string $message error message
     * @param array $context subject
     *
     * @return void
     */
    public function capture($level, string $message, array $context): void
    {
        $event = new Event('Bugsnag.Client.beforeCapture', $this, $context);
        $this->getEventManager()->dispatch($event);

        $exception = Hash::get($context, 'exception');
        if ($exception) {
            if ($exception instanceof PHP7ErrorException) {
                $exception = $exception->getError();
            }
            $lastEventId = $this->getClient()->notifyException($exception);
        } else {
            $stacks = array_slice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), 3);
            foreach ($stacks as $stack) {
                $method = isset($stack['class']) ? "{$stack['class']}::{$stack['function']}" : $stack['function'];
                unset($stack['class']);
                unset($stack['function']);
                
                $this->getClient()->leaveBreadcrumb('method',\Bugsnag\Breadcrumbs\Breadcrumb::ERROR_TYPE
                ,['method'=>$method,'level'=>$level,'stack'=>$stack]);
            }
            
            /*if (method_exists(Severity::class, $level)) {
                $severity = (Severity::class . '::' . $level)();
            } else {
                $severity = Severity::fromError($level);
            }*/
            
            $lastEventId = $this->getClient()->notifyError($level,$message);
        }

        $context['lastEventId'] = $lastEventId;
        $event = new Event('Bugsnag.Client.afterCapture', $this, $context);
        $this->getEventManager()->dispatch($event);
    }

    /**
     * Construct Raven_Client and inject config.
     *
     * @return void
     */
    protected function setupClient()
    {
        $config = (array)Configure::read('Bugsnag');
        if (!Hash::check($config, 'apiKey')) {
            throw new RuntimeException('Bugsnag apiKey not provided.');
        }
        if (!Hash::get($config, 'before_send')) {
            $config['before_send'] = function () {
                $event = new Event('Bugsnag.Client.afterCapture', $this, func_get_args());
                $this->getEventManager()->dispatch($event);
            };
        }

        $event = new Event('Bugsnag.Client.afterSetup', $this);
        $this->getEventManager()->dispatch($event);
    }
}
