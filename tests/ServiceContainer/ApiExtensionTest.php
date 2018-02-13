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

use ApiExtension\ServiceContainer\ApiExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ApiExtensionTest extends TestCase
{
    /**
     * @var ApiExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->extension = new ApiExtension();
    }

    public function testGetConfigKey()
    {
        $this->assertEquals('api', $this->extension->getConfigKey());
    }

    public function testLoad()
    {
        $containerMock = $this->prophesize(ContainerBuilder::class);
        $definitionMock = $this->prophesize(Definition::class);
        $extensionMock = $this->prophesize(ExtensionInterface::class);

        $containerMock->hasExtension('http://symfony.com/schema/dic/services')->willReturn(true)->shouldBeCalledTimes(1);
        $containerMock->getExtension('http://symfony.com/schema/dic/services')->willReturn($extensionMock)->shouldBeCalledTimes(1);
        $extensionMock->getXsdValidationBasePath()->willReturn(false)->shouldBeCalledTimes(1);
        $containerMock->fileExists(Argument::type('string'))->willReturn(false)->shouldBeCalledTimes(1);
        $containerMock->setDefinition(Argument::type('string'), Argument::any())->shouldBeCalled();
        $containerMock->findTaggedServiceIds(Argument::type('string'), true)->willReturn([])->shouldBeCalledTimes(4);
        $containerMock->setParameter('coop_tilleuls.api_extension.default_locale', 'fr_FR')->shouldBeCalledTimes(1);
        $containerMock->getDefinition(Argument::type('string'))->willReturn($definitionMock)->shouldBeCalledTimes(7);
        $definitionMock->setArgument(Argument::type('string'), Argument::type('array'))->shouldBeCalledTimes(5);
        $containerMock->setDefinition('foo', Argument::type(Definition::class))->shouldBeCalledTimes(1);
        $containerMock->setDefinition('bar', Argument::type(Definition::class))->shouldBeCalledTimes(1);
        $definitionMock->addMethodCall('addProvider', Argument::type('array'))->shouldBeCalledTimes(2);

        $this->extension->load($containerMock->reveal(), ['default_locale' => 'fr_FR', 'services' => [], 'providers' => ['foo', 'bar']]);
    }
}
