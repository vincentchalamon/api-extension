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

namespace ApiExtension\SchemaGenerator;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ErrorSchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

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
                    'pattern' => sprintf('^%s$', $this->router->generate('api_jsonld_context', ['shortName' => 'ConstraintViolationList'])),
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
