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

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ErrorSchemaGenerator implements SchemaGeneratorInterface
{
    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return is_a($reflectionClass->getName(), ConstraintViolationListInterface::class, true);
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        return [
            'type' => 'object',
            'properties' => [
                '@context' => [
                    'pattern' => '^/contexts/ConstraintViolationList$',
                ],
                '@type' => [
                    'pattern' => '^ConstraintViolationList$',
                ],
                'hydra:title' => [
                    'pattern' => '^An error occurred$',
                ],
                'hydra:description' => [
                    'type' => 'string',
                ],
                'violations' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'propertyPath' => [
                                'type' => 'string',
                            ],
                            'message' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
