# CakePHP Bugsnag Plugin
CakePHP integration for Bugsnag.

## Requirements
- PHP 7.1+
- CakePHP 4.0+
- and [Bugsnag](https://Bugsnag.io) account


## Installation
### With composer install.
```
composer require ldubois/cake-Bugsnag
```

## Usage

### Set config files.
Write your Bugsnag account info.
```php
// in `config/app.php`
return [
  'Bugsnag' => [
    'ApiKey' => YOUR_Bugsnag_ApiKey
  ]
];
```

### Loading plugin.
In Application.php
```php
public function bootstrap()
{
    parent::bootstrap();

    $this->addPlugin(\ldubois\Bugsnag\Plugin::class);
}
```

Or use cake command.
```
bin/cake plugin load ldubois/Bugsnag --bootstrap
```

That's all! :tada:

### Advanced Usage

#### Ignore noisy exceptions
You can filter out exceptions that make a fuss and harder to determine the issues to address(like PageNotFoundException)
Set exceptions not to log in `Error.skipLog`.

ex)
```php
// in `config/app.php`
'Error' => [
    'skipLog' => [
        NotFoundException::class,
        MissingRouteException::class,
        MissingControllerException::class,
    ],
]
```

ref: CakePHP Cookbook  
https://book.cakephp.org/4/en/development/errors.html#error-exception-configuration



