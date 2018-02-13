<?php

declare(strict_types=1);

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiExtension\SchemaGenerator;

use ApiExtension\Exception\SchemaGeneratorNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class SchemaGeneratorChain implements SchemaGeneratorInterface
{
    /**
     * @var SchemaGeneratorInterface[]
     */
    private $generators;

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if ($generator instanceof SchemaGeneratorAwareInterface) {
                $generator->setSchemaGenerator($this);
            }
        }
        $this->generators = $generators;
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return true;
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        foreach ($this->generators as $schemaGenerator) {
            if ($schemaGenerator->supports($reflectionClass, $context)) {
                return $schemaGenerator->generate($reflectionClass, $context);
            }
        }

        throw new SchemaGeneratorNotFoundException('No schema generator found for class '.$reflectionClass->getName());
    }
}
