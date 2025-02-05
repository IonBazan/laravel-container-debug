# Laravel Container Debug command

[![Laravel 5.4 - 12.x](https://img.shields.io/badge/Laravel-5.4_--_12.x-informational.svg)](http://laravel.com)
[![Latest version](https://img.shields.io/packagist/v/ion-bazan/laravel-container-debug.svg)](https://packagist.org/packages/ion-bazan/laravel-container-debug)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/IonBazan/laravel-container-debug/test.yml)](https://github.com/IonBazan/laravel-container-debug/actions)
[![PHP version](https://img.shields.io/packagist/php-v/ion-bazan/laravel-container-debug.svg)](https://packagist.org/packages/ion-bazan/laravel-container-debug)
[![Codecov](https://img.shields.io/codecov/c/gh/IonBazan/laravel-container-debug)](https://codecov.io/gh/IonBazan/laravel-container-debug)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FIonBazan%2Flaravel-container-debug%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/IonBazan/laravel-container-debug/master)
[![Downloads](https://img.shields.io/packagist/dt/ion-bazan/laravel-container-debug.svg)](https://packagist.org/packages/ion-bazan/laravel-container-debug)
[![License](https://img.shields.io/packagist/l/ion-bazan/laravel-container-debug.svg)](https://packagist.org/packages/ion-bazan/laravel-container-debug)

Symfony-inspired package to list available services in Laravel IoC Container. Works with Laravel 5.4-12.x.

# Example output

```
+--------------------------------------------------------------+-------------------------------------------------------------+--------+-------+
| Service ID                                                   | Class                                                       | Shared | Alias |
+--------------------------------------------------------------+-------------------------------------------------------------+--------+-------+
| IonBazan\Laravel\ContainerDebug\Tests\IContainerContractStub | IonBazan\Laravel\ContainerDebug\Tests\ContainerConcreteStub | No     | No    |
| IonBazan\Laravel\ContainerDebug\Tests\SingletonService       | IonBazan\Laravel\ContainerDebug\Tests\SingletonService      | Yes    | No    |
| alias.b                                                      | alias for "service.b"                                       | No     | Yes   |
| alias.c                                                      | alias for "service.c"                                       | No     | Yes   |
| service.a                                                    | IonBazan\Laravel\ContainerDebug\Tests\ServiceStubA          | No     | No    |
| service.b                                                    | IonBazan\Laravel\ContainerDebug\Tests\ServiceStubB          | No     | No    |
| service.c                                                    | IonBazan\Laravel\ContainerDebug\Tests\ServiceStubC          | No     | No    |
| service.d                                                    | N/A                                                         | No     | No    |
| simple.value.array                                           | <array> [10,20]                                             | No     | No    |
| simple.value.int                                             | <integer> 10                                                | No     | No    |
| simple.value.string                                          | <string> test                                               | No     | No    |
+--------------------------------------------------------------+-------------------------------------------------------------+--------+-------+
```

# Installation

```bash
composer require --dev ion-bazan/laravel-container-debug
```

Thanks to Laravel's Package Auto-Discovery, you don't need to register the ServiceProvider.

## Laravel without auto-discovery

If you don't use auto-discovery, add the Service Provider to the `providers` array in your `config/app.php`:

```php
\IonBazan\Laravel\ContainerDebug\ServiceProvider::class,
```

## Lumen

For Lumen, register the Service Provider in your `bootstrap/app.php`:
```php
$app->register(\IonBazan\Laravel\ContainerDebug\ServiceProvider::class);
```

# Usage

**TL;DR:** `php artisan container:debug --help`

Usage is pretty straightforward:

 - to list all services: `php artisan container:debug`
 - to check specific service: `php artisan container:debug MyFooService`
 - to list all tags with tagged services: `php artisan container:debug --tags`
 - to list all services with tag: `php artisan container:debug --tag=foo`

The command does it best to find the service you want by stripping slashes in service names and looking for any occurrences of given name in service names.
For example, to display `service.foo` service information, simply use `php artisan container:debug foo`. If there are more services with the similar name, a prompt will be shown to choose the one you are looking for.

To get the class name of the service, it must be initialized. To check how much time it took for each service, you may use `--profile` option.
