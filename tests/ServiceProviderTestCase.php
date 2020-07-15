<?php

namespace IonBazan\Laravel\ContainerDebug\Tests;

use Illuminate\Contracts\Foundation\Application;
use IonBazan\Laravel\ContainerDebug\ServiceProvider;
use PHPUnit\Framework\TestCase;

class ServiceProviderTestCase extends TestCase
{
    public function testItRegistersCommands()
    {
        $app = $this->getMockBuilder(Application::class)->setMethods(['runningInConsole'])->getMockForAbstractClass();
        $sp = $this->getMockBuilder(ServiceProvider::class)->setMethods(['commands'])->setConstructorArgs([$app])->getMock();
        $app->expects($this->once())->method('runningInConsole')->willReturn(true);
        $sp->expects($this->once())->method('commands');
        $sp->boot();
    }

    public function testItDoesNotRegisterCommands()
    {
        $app = $this->getMockBuilder(Application::class)->setMethods(['runningInConsole'])->getMockForAbstractClass();
        $sp = $this->getMockBuilder(ServiceProvider::class)->setMethods(['commands'])->setConstructorArgs([$app])->getMock();
        $app->expects($this->once())->method('runningInConsole')->willReturn(false);
        $sp->expects($this->never())->method('commands');
        $sp->boot();
    }
}
