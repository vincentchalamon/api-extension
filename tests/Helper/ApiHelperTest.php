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

namespace ApiExtension\Tests\Helper;

use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Guesser\GuesserInterface;
use ApiExtension\Transformer\TransformerInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ApiHelperTest extends TestCase
{
    /**
     * @var ApiHelper
     */
    private $helper;

    /**
     * @var ObjectProphecy|TransformerInterface
     */
    private $transformerMock;

    /**
     * @var ObjectProphecy|GuesserInterface
     */
    private $guesserMock;

    /**
     * @var ObjectProphecy|IriConverterInterface
     */
    private $iriConverterMock;

    /**
     * @var ObjectProphecy|ManagerRegistry
     */
    private $registryMock;

    /**
     * @var ObjectProphecy|PropertyInfoExtractorInterface
     */
    private $propertyInfoMock;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->transformerMock = $this->prophesize(TransformerInterface::class);
        $this->guesserMock = $this->prophesize(GuesserInterface::class);
        $this->iriConverterMock = $this->prophesize(IriConverterInterface::class);
        $this->registryMock = $this->prophesize(ManagerRegistry::class);
        $this->propertyInfoMock = $this->prophesize(PropertyInfoExtractorInterface::class);

        $this->helper = new ApiHelper($this->transformerMock->reveal(), $this->guesserMock->reveal());
        $this->helper->setIriConverter($this->iriConverterMock->reveal());
        $this->helper->setRegistry($this->registryMock->reveal());
        $this->helper->setPropertyInfo($this->propertyInfoMock->reveal());
    }

    public function testApiHelperRetrievesUriFromResourceClass()
    {
        $reflectionClassMock = $this->prophesize(\ReflectionClass::class);
        $reflectionClassMock->getName()->willReturn('foo')->shouldBeCalledTimes(1);
        $this->iriConverterMock->getIriFromResourceClass('foo')->willReturn('bar')->shouldBeCalledTimes(1);
        $this->assertEquals('bar', $this->helper->getUri($reflectionClassMock->reveal()));
    }
}
