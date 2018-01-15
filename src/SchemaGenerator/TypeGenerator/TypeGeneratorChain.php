<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\SchemaGenerator\TypeGenerator;

use ApiExtension\Exception\TypeGeneratorNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TypeGeneratorChain implements TypeGeneratorInterface
{
    /**
     * @var TypeGeneratorInterface[]
     */
    private $generators;

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if ($generator instanceof TypeGeneratorAwareInterface) {
                $generator->setTypeGenerator($this);
            }
        }
        $this->generators = $generators;
    }

    public function supports(string $property, array $mapping, array $context = []): bool
    {
        return true;
    }

    public function generate(string $property, array $mapping, array $context = []): array
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($property, $mapping, $context)) {
                return $generator->generate($property, $mapping, $context);
            }
        }

        throw new TypeGeneratorNotFoundException('No type generator found for mapping: '.print_r($mapping, true));
    }
}
