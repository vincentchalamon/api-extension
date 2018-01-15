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

namespace ApiExtension\SchemaGenerator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ItemSchemaGenerator implements SchemaGeneratorInterface, SchemaGeneratorAwareInterface
{
    use SchemaGeneratorAwareTrait;

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return false === ($context['collection'] ?? false) && true === ($context['root'] ?? false);
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        return array_merge_recursive($this->schemaGenerator->generate($reflectionClass), [
            'properties' => [
                '@context' => [
                    'type' => 'string',
                    'pattern' => sprintf('^/contexts/%s$', $reflectionClass->getShortName()),
                ],
            ],
        ]);
    }
}
