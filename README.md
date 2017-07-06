# Backoff, Simple backoff / retry functionality

[![License](https://poser.pugx.org/yriveiro/php-backoff/license)](https://packagist.org/packages/yriveiro/php-backoff) [![Build Status](https://travis-ci.org/yriveiro/php-backoff.svg?branch=master)](https://travis-ci.org/yriveiro/php-backoff) [![Coverage Status](https://coveralls.io/repos/github/yriveiro/php-backoff/badge.svg)](https://coveralls.io/github/yriveiro/php-backoff) [![Total Downloads](https://poser.pugx.org/yriveiro/php-backoff/downloads)](https://packagist.org/packages/yriveiro/php-backoff) [![HHVM Status](http://hhvm.h4cc.de/badge/yriveiro/php-backoff.svg)](http://hhvm.h4cc.de/package/yriveiro/php-backoff)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0f1a7b44-98e9-4577-819f-7df811338082/mini.png)](https://insight.sensiolabs.com/projects/0f1a7b44-98e9-4577-819f-7df811338082)

**NOTE**: to use php-backoff with PHP 5.x please use the lasted release of branch 1.x

# API

### getDefaultOptions():

This method is static and returns an array with the default options:
- `cap`: Max duration allowed (in microseconds). If backoff duration is greater than cap, cap is returned, default is `1000000` microseconds.
- `maxAttempts`: Number of attempts before thrown an Yriveiro\Backoff\BackoffException. Default is `0`, no limit.

### exponential($attempt):

This method use and exponential function `E(attempt) = (2**attempt - 1)` to calculate backoff time.

#### Parameters
- `attempt`: incremental value that represents the current retry number.

### equalJitter($attempt);

Exponential backoff has one disadvantage. In high concurrence, we can have multiples calls with the same backoff time due the time is highly bound to the current attempt, different calls could be in the same attempt.

To solve this we can add a jitter value to allow some randomization.

`equalJitter` uses the function: `E(attempt) = min(((2**attempt - 1) / 2), random(0, ((2**attempt - 1) / 2)))`.

#### Parameters
- `attempt`: incremental value that represents the current retry number.

### fullJitter($attempt);

Full jitter behaves like `equalJitter` method, the main difference between them is the way in how the jitter value is calculated.

`fullJitter` uses the function: `E(attempt) = min(random(0, (2**attempt - 1) / 2))`.

#### Parameters
- `attempt`: incremental value that represents the current retry number.

# Usage

### Zero configuration examples:

With zero configuration, we will never stop to try fetch data. The exit condition is your responsibility.

```php
$attempt = 1;
$backoff = new Backoff();

$response = $http->get('http://myservice.com/user/1');

while (!$response) {
    $time = $backoff->exponential($attempt);
    $attempt++;

    usleep($time);

    $response = $http->get('http://myservice.com/user/1');
}
```

### With configuration examples:

```php
$attempt = 1;
$options = Backoff::getDefaultOptions();
$options['maxAttempts'] = 3;

$backoff = new Backoff($options);

$response = $http->get('http://myservice.com/user/1');

try
    while (!$response) {
        $time = $backoff->fullJitter($attempt);
        $attempt++;

        usleep($time);

        $response = $http->get('http://myservice.com/user/1');
    }
} catch (Yriveiro\Backoff\BackoffException $e) {
    // Handle the exception
}
```

# Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/download/).

```sh
php composer.phar require "yriveiro/php-backoff"
```

# Tests

Tests are performed using the `phpunit` library, to run them:

```sh
php vendor/bin/phpunit tests
```

# Know issues

None.

# How to contribute

Have an idea? Found a bug?, contributions are welcome :)

# License

Backoff is licensed under MIT license.

