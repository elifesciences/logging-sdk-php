eLife logging SDK for PHP
=========================

[![Build Status](https://ci--alfred.elifesciences.org/buildStatus/icon?job=library-logging-sdk-php)](https://ci--alfred.elifesciences.org/job/library-logging-sdk-php/)

This library provides logging for the eLife Sciences applications.

Dependencies
------------

* [Composer](https://getcomposer.org/)
* PHP 7

Installation
-------------

`composer require elife/logging-sdk`

Set up
------

### Silex

```php
use eLife\Logging\Silex\LoggerProvider;

$app->register(new LoggerProvider());
```

Running the tests
-----------------

`vendor/bin/phpunit`
