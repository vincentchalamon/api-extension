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

use ApiExtension\SchemaGenerator\SchemaGeneratorAwareInterface;
use ApiExtension\SchemaGenerator\SchemaGeneratorAwareTrait;
use ApiExtension\SchemaGenerator\SchemaGeneratorInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionSchemaGenerator implements SchemaGeneratorInterface, SchemaGeneratorAwareInterface
{
    use SchemaGeneratorAwareTrait;

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        $resourceMetadata = $this->metadataFactory->create($reflectionClass->name);
        $normalizationContext = $resourceMetadata->getCollectionOperationAttribute('get', 'normalization_context', [], true);

        $schema = [
            'type' => 'object',
            'properties' => [
                '@context' => [
                    'type' => 'string',
                    'pattern' => sprintf('^%s$', $this->router->generate('api_jsonld_context', ['shortName' => $reflectionClass->getShortName()])),
                ],
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
                    'items' => $this->schemaGenerator->generate($reflectionClass, ['serializer_groups' => $normalizationContext['groups'] ?? []]),
                ],
                // todo Add hydra:view (cf. crud.feature l. 94)
                // todo Add hydra:search (cf. crud.feature l. 94)
            ],
            'required' => ['@context', '@id', '@type', 'hydra:member'],
        ];
        if (!$resourceMetadata->getAttribute('pagination_partial', false)) {
            $schema['properties']['hydra:totalItems'] = ['type' => 'integer'];
            $schema['required'][] = 'hydra:totalItems';
        }

        return $schema;
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return true === ($context['collection'] ?? false);
    }
}
