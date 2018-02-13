<?php

/*
 * This file is part of the API Extension project.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Tests\ServiceContainer;

use ApiExtension\ServiceContainer\ApiConfigurator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ApiConfiguratorTest extends TestCase
{
    public function testConfigure()
    {
        $kernelMock = $this->prophesize(KernelInterface::class);
        $containerMock = $this->prophesize(ContainerInterface::class);
        $serviceMock = $this->prophesize(ServiceInterface::class);

        $kernelMock->getContainer()->willReturn($containerMock)->shouldBeCalledTimes(1);
        $containerMock->getParameter('parameter')->willReturn('foo')->shouldBeCalledTimes(1);
        $containerMock->get('service')->willReturn('bar')->shouldBeCalledTimes(1);
        $serviceMock->setParameter('foo')->shouldBeCalledTimes(1);
        $serviceMock->setService('bar')->shouldBeCalledTimes(1);

        $configurator = new ApiConfigurator($kernelMock->reveal(), [
            'service' => '@service',
            'parameter' => '%parameter%',
        ]);
        $configurator->configure($serviceMock->reveal());
    }
}

interface ServiceInterface
{
    public function setParameter(string $parameter): void;

    public function setService(string $service): void;
}
