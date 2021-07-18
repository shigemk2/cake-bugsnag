<?php
namespace Shigemk2\Bugsnag;

use Cake\Core\Configure;
use Cake\Log\Log;
use Shigemk2\Bugsnag\Error\ConsoleErrorHandler;
use Shigemk2\Bugsnag\Error\ErrorHandler;
use Shigemk2\Bugsnag\Log\Engine\BugsnagLog;

$isCli = PHP_SAPI === 'cli';
if (!$isCli && strpos((env('argv')[0] ?? ''), '/phpunit') !== false) {
    $isCli = true;
}
if ($isCli) {
    (new ConsoleErrorHandler(Configure::read('Error', [])))->register();
} else {
    (new ErrorHandler(Configure::read('Error', [])))->register();
}


