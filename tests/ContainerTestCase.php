<?php

namespace IonBazan\Laravel\ContainerDebug\Tests;

use Illuminate\Container\Container;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\TestCase;

abstract class ContainerTestCase extends TestCase
{
    protected function getTestContainer(): Container
    {
        $container = new class extends Container {
            public function runningUnitTests(): bool // required for Laravel 11
            {
                return true;
            }
        };

        $container->bind('service.d', ServiceStubD::class);
        $container->bind('service.c', ServiceStubC::class);
        $container->when(ServiceStubD::class)->needs('$test')->give('test'); // Invalid argument provided, should fail
        $container->bind('service.b', ServiceStubB::class);
        $container->bind('service.a', ServiceStubA::class);
        $container->alias('service.c', 'alias.c');
        $container->alias('service.b', 'alias.b');
        $container->singleton('\\RootClass');
        $container->bind(IContainerContractStub::class, ContainerConcreteStub::class);
        $container->singleton(SingletonService::class);
        $container->tag(['service.c', 'service.b'], ['tag2']);
        $container->tag(['service.b', 'service.a'], ['tag1']);
        $container->tag([], ['tag3']);
        $container->bind('simple.value.string', static function () {
            return 'test';
        });
        $container->bind('simple.value.int', static function () {
            return 10;
        });
        $container->bind('simple.value.array', static function () {
            return [10, 20];
        });

        return $container;
    }

    /**
     * BC layer.
     */
    protected static function assertContainsString(string $needle, string $haystack, string $message = '')
    {
        self::assertThat($haystack, new StringContains($needle), $message);
    }

    /**
     * BC layer.
     */
    protected static function assertNotContainsString(string $needle, string $haystack, string $message = '')
    {
        self::assertThat($haystack, new LogicalNot(new StringContains($needle)), $message);
    }
}

class ContainerConcreteStub implements IContainerContractStub
{
}

interface IContainerContractStub
{
}

class SingletonService
{
}

class ServiceStubA
{
}

class ServiceStubB
{
}

class ServiceStubC
{
}

class ServiceStubD
{
    public function __construct(array $test)
    {
    }
}
