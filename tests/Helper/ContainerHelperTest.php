<?php

namespace IonBazan\Laravel\ContainerDebug\Tests\Helper;

use Illuminate\Container\Container;
use IonBazan\Laravel\ContainerDebug\Helper\ContainerHelper;
use IonBazan\Laravel\ContainerDebug\Tests\ContainerConcreteStub;
use IonBazan\Laravel\ContainerDebug\Tests\ContainerTestCase;
use IonBazan\Laravel\ContainerDebug\Tests\IContainerContractStub;
use IonBazan\Laravel\ContainerDebug\Tests\ServiceStubA;
use IonBazan\Laravel\ContainerDebug\Tests\SingletonService;
use Symfony\Bridge\PhpUnit\SetUpTearDownTrait;

class ContainerHelperTest extends ContainerTestCase
{
    use SetUpTearDownTrait;

    /**
     * @var ContainerHelper
     */
    protected $helper;

    protected function doSetUp()
    {
        $this->helper = new ContainerHelper($this->getTestContainer());
    }

    public function testGetAllServices()
    {
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
        ], $this->helper->getAllServices());

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
        ], $this->helper->getAllServices(false));
    }

    public function testGetAliases()
    {
        self::assertSame(['alias.b', 'alias.c'], $this->helper->getAliases());
    }

    public function testGetTaggedServices()
    {
        self::assertSame(['service.a', 'service.b'], $this->helper->getTaggedServices('tag1'));
        self::assertSame(['service.b', 'service.c'], $this->helper->getTaggedServices('tag2'));
        self::assertSame([], $this->helper->getTaggedServices('tag3'));
        self::assertSame([], $this->helper->getTaggedServices('invalid-tag'));
    }

    public function testGetAllTags()
    {
        self::assertSame(['tag1', 'tag2', 'tag3'], $this->helper->getAllTags());
    }

    public function testGetTagsForService()
    {
        self::assertSame(['tag1', 'tag2'], $this->helper->getServiceTags('service.b'));
        self::assertSame(['tag1'], $this->helper->getServiceTags('service.a'));
        self::assertSame(['tag2'], $this->helper->getServiceTags('service.c'));
        self::assertSame([], $this->helper->getServiceTags('service.d'));
    }

    public function testGetContainer()
    {
        $container = new Container();
        $helper = new ContainerHelper($container);
        self::assertSame($container, $helper->getContainer());
    }

    public function testGetClassNameForExistingClass()
    {
        self::assertSame(ServiceStubA::class, $this->helper->getClassNameDescription('service.a'));
        self::assertSame(ContainerConcreteStub::class, $this->helper->getClassNameDescription(IContainerContractStub::class));
    }

    public function testGetClassNameForAlias()
    {
        self::assertSame('alias for "service.b"', $this->helper->getClassNameDescription('alias.b'));
        self::assertSame('alias for "service.c"', $this->helper->getClassNameDescription('alias.c'));
    }

    public function testGetClassNameForSimpleValue()
    {
        self::assertSame('<array> [10,20]', $this->helper->getClassNameDescription('simple.value.array'));
        self::assertSame('<integer> 10', $this->helper->getClassNameDescription('simple.value.int'));
        self::assertSame('<string> test', $this->helper->getClassNameDescription('simple.value.string'));
    }

    public function testGetClassNameForInvalidService()
    {
        self::assertSame('N/A', $this->helper->getClassNameDescription('invalid-service'));
    }
}
