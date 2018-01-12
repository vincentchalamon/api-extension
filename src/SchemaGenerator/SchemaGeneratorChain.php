<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\SchemaGenerator;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class SchemaGeneratorChain implements SchemaGeneratorInterface
{
    /**
     * @var SchemaGeneratorInterface[]
     */
    private $schemaGenerators;

    public function __construct(array $schemaGenerators)
    {
        foreach ($schemaGenerators as $schemaGenerator) {
            if ($schemaGenerator instanceof SchemaGeneratorAwareInterface) {
                $schemaGenerator->setSchemaGenerator($this);
            }
        }
        $this->schemaGenerators = $schemaGenerators;
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return true;
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        foreach ($this->schemaGenerators as $schemaGenerator) {
            if ($schemaGenerator->supports($reflectionClass, $context)) {
                return $schemaGenerator->generate($reflectionClass, $context);
            }
        }

        // todo Custom exception
        throw new \Exception();
    }
}
