<?php

namespace Shigemk2\Bugsnag\Error;

use Cake\Error\ConsoleErrorHandler as CakeConsoleErrorHandler;

class ConsoleErrorHandler extends CakeConsoleErrorHandler
{
    use BugsnagErrorHandlerTrait;
}
