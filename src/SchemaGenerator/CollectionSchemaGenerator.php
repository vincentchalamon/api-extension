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

use ApiExtension\Helper\UriHelper;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class CollectionSchemaGenerator implements SchemaGeneratorInterface, SchemaGeneratorAwareInterface
{
    use SchemaGeneratorAwareTrait;

    private $helper;

    public function __construct(UriHelper $helper)
    {
        $this->helper = $helper;
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return true === ($context['collection'] ?? false);
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        return [
            'type' => 'object',
            'properties' => [
                '@id' => [
                    'type' => 'string',
                    'pattern' => sprintf('^%s$', $this->helper->getUri($reflectionClass)),
                ],
                '@type' => [
                    'type' => 'string',
                    'pattern' => '^hydra:Collection$',
                ],
                'hydra:member' => [
                    'type' => 'array',
                    'items' => $this->schemaGenerator->generate($reflectionClass),
                ],
                'hydra:totalItems' => [
                    'type' => 'integer',
                ],
                // todo Add hydra:view (cf. crud.feature l. 94)
                // todo Add hydra:search (cf. crud.feature l. 94)
            ],
        ];
    }
}
