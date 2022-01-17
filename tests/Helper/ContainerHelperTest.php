<?php

namespace IonBazan\Laravel\ContainerDebug\Tests\Helper;

use Illuminate\Container\Container;
use IonBazan\Laravel\ContainerDebug\Helper\ContainerHelper;
use IonBazan\Laravel\ContainerDebug\Tests\ContainerConcreteStub;
use IonBazan\Laravel\ContainerDebug\Tests\ContainerTestCase;
use IonBazan\Laravel\ContainerDebug\Tests\IContainerContractStub;
use IonBazan\Laravel\ContainerDebug\Tests\ServiceStubA;
use IonBazan\Laravel\ContainerDebug\Tests\SingletonService;

class ContainerHelperTest extends ContainerTestCase
{
    public function testGetAllServices()
    {
        $helper = $this->getContainerHelper();

        self::assertSame([
            IContainerContractStub::class,
            SingletonService::class,
            '\\RootClass',
            'alias.b',
            'alias.c',
            'service.a',
            'service.b',
            'service.c',
            'service.d',
            'simple.value.array',
            'simple.value.int',
            'simple.value.string',
        ], $helper->getAllServices());

        self::assertSame([
            IContainerContractStub::class,
            SingletonService::class,
            '\\RootClass',
            'service.a',
            'service.b',
            'service.c',
            'service.d',
            'simple.value.array',
            'simple.value.int',
            'simple.value.string',
        ], $helper->getAllServices(false));
    }

    public function testGetAliases()
    {
        self::assertSame(['alias.b', 'alias.c'], $this->getContainerHelper()->getAliases());
    }

    public function testGetTaggedServices()
    {
        $helper = $this->getContainerHelper();
        self::assertSame(['service.a', 'service.b'], $helper->getTaggedServices('tag1'));
        self::assertSame(['service.b', 'service.c'], $helper->getTaggedServices('tag2'));
        self::assertSame([], $helper->getTaggedServices('tag3'));
        self::assertSame([], $helper->getTaggedServices('invalid-tag'));
    }

    public function testGetAllTags()
    {
        self::assertSame(['tag1', 'tag2', 'tag3'], $this->getContainerHelper()->getAllTags());
    }

    public function testGetTagsForService()
    {
        $helper = $this->getContainerHelper();
        self::assertSame(['tag1', 'tag2'], $helper->getServiceTags('service.b'));
        self::assertSame(['tag1'], $helper->getServiceTags('service.a'));
        self::assertSame(['tag2'], $helper->getServiceTags('service.c'));
        self::assertSame([], $helper->getServiceTags('service.d'));
    }

    public function testGetContainer()
    {
        $container = new Container();
        $helper = new ContainerHelper($container);
        self::assertSame($container, $helper->getContainer());
    }

    public function testGetClassNameForExistingClass()
    {
        $helper = $this->getContainerHelper();
        self::assertSame(ServiceStubA::class, $helper->getClassNameDescription('service.a'));
        self::assertSame(ContainerConcreteStub::class, $helper->getClassNameDescription(IContainerContractStub::class));
    }

    public function testGetClassNameForAlias()
    {
        $helper = $this->getContainerHelper();
        self::assertSame('alias for "service.b"', $helper->getClassNameDescription('alias.b'));
        self::assertSame('alias for "service.c"', $helper->getClassNameDescription('alias.c'));
    }

    public function testGetClassNameForSimpleValue()
    {
        $helper = $this->getContainerHelper();
        self::assertSame('<array> [10,20]', $helper->getClassNameDescription('simple.value.array'));
        self::assertSame('<integer> 10', $helper->getClassNameDescription('simple.value.int'));
        self::assertSame('<string> test', $helper->getClassNameDescription('simple.value.string'));
    }

    public function testGetClassNameForInvalidService()
    {
        self::assertSame('N/A', $this->getContainerHelper()->getClassNameDescription('invalid-service'));
    }

    private function getContainerHelper(): ContainerHelper
    {
        return new ContainerHelper($this->getTestContainer());
    }
}
