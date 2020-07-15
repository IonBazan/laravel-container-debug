<?php

namespace IonBazan\Laravel\ContainerDebug\Tests\Command;

use Illuminate\Contracts\Container\Container;
use IonBazan\Laravel\ContainerDebug\Command\ContainerDebugCommand;
use IonBazan\Laravel\ContainerDebug\Tests\ContainerConcreteStub;
use IonBazan\Laravel\ContainerDebug\Tests\ContainerTestCase;
use IonBazan\Laravel\ContainerDebug\Tests\ServiceStubA;
use IonBazan\Laravel\ContainerDebug\Tests\SingletonService;
use RuntimeException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group time-sensitive
 */
class ContainerDebugCommandTest extends ContainerTestCase
{
    public function testOutputsAllServicesInAlphabeticalOrder()
    {
        $tester = $this->getCommandTester();
        self::assertSame(0, $tester->execute([]));
        $display = $tester->getDisplay();
        $output = <<<OUTPUT
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

OUTPUT;
        self::assertSame($output, $display);
    }

    public function testShowsProfilingInformation()
    {
        $tester = $this->getCommandTester();
        self::assertSame(0, $tester->execute(['--profile' => true]));
        $display = $tester->getDisplay();
        $output = <<<OUTPUT
+--------------------------------------------------------------+-------------------------------------------------------------+--------+-------+-----------------+
| Service ID                                                   | Class                                                       | Shared | Alias | Resolution time |
+--------------------------------------------------------------+-------------------------------------------------------------+--------+-------+-----------------+
| IonBazan\Laravel\ContainerDebug\Tests\IContainerContractStub | IonBazan\Laravel\ContainerDebug\Tests\ContainerConcreteStub | No     | No    | 0               |
| IonBazan\Laravel\ContainerDebug\Tests\SingletonService       | IonBazan\Laravel\ContainerDebug\Tests\SingletonService      | Yes    | No    | 0               |
| alias.b                                                      | alias for "service.b"                                       | No     | Yes   | 0               |
| alias.c                                                      | alias for "service.c"                                       | No     | Yes   | 0               |
| service.a                                                    | IonBazan\Laravel\ContainerDebug\Tests\ServiceStubA          | No     | No    | 0               |
| service.b                                                    | IonBazan\Laravel\ContainerDebug\Tests\ServiceStubB          | No     | No    | 0               |
| service.c                                                    | IonBazan\Laravel\ContainerDebug\Tests\ServiceStubC          | No     | No    | 0               |
| service.d                                                    | N/A                                                         | No     | No    | 0               |
| simple.value.array                                           | <array> [10,20]                                             | No     | No    | 0               |
| simple.value.int                                             | <integer> 10                                                | No     | No    | 0               |
| simple.value.string                                          | <string> test                                               | No     | No    | 0               |
+--------------------------------------------------------------+-------------------------------------------------------------+--------+-------+-----------------+

OUTPUT;
        self::assertSame($output, $display);
    }

    public function testOutputsSpecificService()
    {
        $tester = $this->getCommandTester();
        self::assertSame(0, $tester->execute(['name' => 'service.a']));
        $display = $tester->getDisplay();
        self::assertContainsString('service.a', $display);
        self::assertNotContainsString('service.b', $display);
        self::assertNotContainsString('service.c', $display);
    }

    public function testFindsMatchingServicesIgnoringBackslashes()
    {
        $tester = $this->getCommandTester();
        self::assertSame(0, $tester->execute(['name' => str_replace('\\', '', SingletonService::class)]));
        $display = $tester->getDisplay();
        self::assertContainsString(SingletonService::class, $display);
    }

    public function testAsksForSpecificService()
    {
        $tester = $this->getCommandTester();
        $tester->setInputs(['0']);
        self::assertSame(0, $tester->execute(['name' => 'service']));
        $display = $tester->getDisplay();
        self::assertContainsString('service.a', $display);
        self::assertContainsString('service.b', $display);
        self::assertContainsString('service.c', $display);
        self::assertContainsString(SingletonService::class, $display);
        self::assertNotContainsString(ContainerConcreteStub::class, $display);
        self::assertNotContainsString(ServiceStubA::class, $display);
        self::assertNotContainsString(ServiceStubB::class, $display);
        self::assertNotContainsString(ServiceStubC::class, $display);
    }

    public function testDoNotAskQuestionsWhenNonInteractive()
    {
        $tester = $this->getCommandTester();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "service" not found');
        $tester->execute(['name' => 'service'], ['interactive' => false]);
    }

    public function testGetsFirstMatchingService()
    {
        $tester = $this->getCommandTester();
        self::assertSame(0, $tester->execute(['name' => 'ContractStub']));
        $display = $tester->getDisplay();
        self::assertContainsString(ContainerConcreteStub::class, $display);
        self::assertNotContainsString('service.b', $display);
        self::assertNotContainsString('service.c', $display);
    }

    public function testThrowsErrorOnInvalidServiceName()
    {
        $tester = $this->getCommandTester();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No services found matching "invalid-service"');
        $tester->execute(['name' => 'invalid-service']);
    }

    public function testListsTags()
    {
        $tester = $this->getCommandTester();
        self::assertSame(0, $tester->execute(['--tags' => true]));
        $display = $tester->getDisplay();
        self::assertContainsString('tag1', $display);
        self::assertContainsString('tag2', $display);
        self::assertContainsString('tag3', $display);
        self::assertContainsString('service.a', $display);
        self::assertContainsString('service.b', $display);
        self::assertContainsString('service.c', $display);
        self::assertNotContainsString(SingletonService::class, $display);
    }

    public function testShowsSingleTag()
    {
        $tester = $this->getCommandTester();
        self::assertSame(0, $tester->execute(['--tag' => 'tag1']));
        $display = $tester->getDisplay();
        self::assertContainsString('tag1', $display);
        self::assertContainsString('service.a', $display);
        self::assertContainsString('service.b', $display);
        self::assertNotContainsString('service.c', $display);
        self::assertNotContainsString('tag2', $display);
        self::assertNotContainsString('tag3', $display);
    }

    public function testItThrowsErrorWhenItsNotLaravelApp()
    {
        $command = new ContainerDebugCommand();
        $container = $this->getMockBuilder(Container::class)->getMock();
        $command->setLaravel($container);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Your application must implement Illuminate\Container\Container');
        $command->handle();
    }

    /**
     * @dataProvider invalidInputDataProvider
     */
    public function testThrowsErrorOnInvalidArgumentsCombination(array $input)
    {
        $tester = $this->getCommandTester();
        $this->expectException(InvalidArgumentException::class);
        $tester->execute($input);
    }

    public function invalidInputDataProvider()
    {
        return [
            'both name and --tags' => [['name' => 'test', '--tags' => true]],
            'both name and --tag' => [['name' => 'test', '--tag' => 'test1']],
            'both --tags and --tag' => [['--tags' => true, '--tag' => 'test1']],
        ];
    }

    private function getCommandTester(): CommandTester
    {
        $command = new ContainerDebugCommand();
        $command->setLaravel($this->getTestContainer());

        return new CommandTester($command);
    }
}
