<?php

namespace Shigemk2\Bugsnag\Error;

use Cake\Core\InstanceConfigTrait;
use Cake\Error\FatalErrorException;
use Cake\Error\PHP7ErrorException;
use Cake\Log\Log;
use ErrorException;
use Exception;

trait BugsnagErrorHandlerTrait
{
    use InstanceConfigTrait;

    /**
     * Generates a formatted error message with exception.
     *
     * @see \Cake\Error\BaseErrorHandler::_getMessage()
     *
     * @param Exception $exception subject
     * @return string Formatted message
     */
    protected function _getMessage(Exception $exception)
    {
        $errorName = 'Exception:';
        if ($exception instanceof FatalErrorException) {
            $errorName = 'Fatal Error:';
        }

        return sprintf(
            "<error>%s</error> %s\nIn [%s, line %s]\n",
            $errorName,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    /* @var Client */
    protected $client;

    /**
     * Change error messages into ErrorException and write exception log.
     *
     * @param string $level The level name of the log.
     * @param array $data Array of error data.
     * @return bool
     */
    protected function _logError($level, array $data): bool
    {
        $error = new ErrorException($data['description'], 0, $data['code'], $data['file'], $data['line']);

        return Log::write($level, $this->_getMessage($error), ['exception' => $error]);
    }

    /**
     * Handles exception logging.
     *
     * @param Exception $exception Exception instance.
     * @return bool
     */
    protected function _logException(Exception $exception)
    {
        $config = $this->_options;

        $unwrapped = $exception instanceof PHP7ErrorException ?
            $exception->getError() :
            $exception;

        if (empty($config['log'])) {
            return false;
        }
        if (!empty($config['skipLog'])) {
            foreach ((array)$config['skipLog'] as $class) {
                if ($unwrapped instanceof $class) {
                    return false;
                }
            }
        }

        $message = $this->_getMessage($exception);

        return Log::error($message, compact('exception'));
    }
}
