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

namespace ApiExtension\Tests\Transformer;

use ApiExtension\Transformer\ArrayTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ArrayTransformerTest extends TestCase
{
    /**
     * @var ArrayTransformer
     */
    private $transformer;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->transformer = new ArrayTransformer();
    }

    /**
     * @dataProvider getTypes
     */
    public function testArrayTransformerSupportsAllTypesOfArray(string $type)
    {
        $this->assertTrue($this->transformer->supports('foo', ['type' => $type], 'bar'));
    }

    public function getTypes(): array
    {
        return [['array'], ['json_array'], ['simple_array']];
    }

    public function testArrayTransformerDoesNotSupportInvalidType()
    {
        $this->assertFalse($this->transformer->supports('foo', ['type' => 'invalid'], 'bar'));
    }

    public function testArrayTransformerDoesNotSupportInvalidValue()
    {
        $this->assertFalse($this->transformer->supports('foo', ['type' => 'array'], null));
    }

    public function testArrayTransformerTransformsStringToArray()
    {
        $this->assertEquals(['foo', 'bar'], $this->transformer->transform('foo', [], ' foo , bar '));
    }
}
