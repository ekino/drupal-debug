# Contributing to this project

## Running tests locally

To run tests locally for a specific version of PHP:

```bash
$ rm -rf vendor composer.lock
$ docker-compose up
$ docker-compose run --user www-data php-$WANTED_PHP_VERSION bash
container$ composer install
container$ vendor/bin/phpunit --no-coverage
```

## Reporting Issues

When reporting issues, please try to be as descriptive as possible, and include
as much relevant information as you can. A step by step guide on how to
reproduce the issue will greatly increase the chances of your issue being
resolved in a timely manner.

