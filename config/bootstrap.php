<?php
namespace Ldubois\Bugsnag;

use Cake\Core\Configure;
use Cake\Log\Log;
use Ldubois\Bugsnag\Error\ConsoleErrorHandler;
use Ldubois\Bugsnag\Error\ErrorHandler;
use Ldubois\Bugsnag\Log\Engine\BugsnagLog;

$isCli = PHP_SAPI === 'cli';
if (!$isCli && strpos((env('argv')[0] ?? ''), '/phpunit') !== false) {
    $isCli = true;
}
if ($isCli) {
    (new ConsoleErrorHandler(Configure::read('Error', [])))->register();
} else {
    (new ErrorHandler(Configure::read('Error', [])))->register();
}


