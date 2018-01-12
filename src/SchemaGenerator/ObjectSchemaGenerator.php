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
final class ObjectSchemaGenerator implements SchemaGeneratorInterface, SchemaGeneratorAwareInterface
{
    use SchemaGeneratorAwareTrait;

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return false === ($context['collection'] ?? false) && false === ($context['root'] ?? false);
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        $className = $reflectionClass->getName();
        $schema = [
            'type' => 'object',
            'properties' => [
                '@id' => [
                    'type' => 'string',
                    'pattern' => sprintf('^%s$', str_ireplace('12345-abcde', '[\\w-]+', $this->getItemUri($name, '12345-abcde'))),
                ],
                '@type' => [
                    'type' => 'string',
                    'pattern' => sprintf('^%s$', $reflectionClass->getShortName()),
                ],
            ],
        ];

        $groups = $this->resourceMetadataFactory->create($className)->getCollectionOperationAttribute('get', 'normalization_context', [], true)['groups'] ?? [];
        foreach ($this->propertyInfoExtractor->getProperties($className, ['serializer_groups' => $groups]) as $property) {
            $types = $this->propertyInfoExtractor->getTypes($className, $property);
            if (!count($types)) {
                continue;
            }
            $type = array_shift($types);
            $schema['properties'][$property] = ['type' => $type->isNullable() ? ['null'] : []];
            $builtinType = $type->getBuiltinType();
            $typeClassName = $type->getClassName();
            if (null === $typeClassName) {
                switch ($builtinType) {
                    default:
                        $schema['properties'][$property]['type'][] = $builtinType;
                        break;
                    case 'int':
                        $schema['properties'][$property]['type'][] = 'integer';
                        break;
                    case 'bool':
                        $schema['properties'][$property]['type'][] = 'boolean';
                        break;
                    case 'float':
                        $schema['properties'][$property]['type'][] = 'number';
                        break;
                }
            } else {
                switch ($typeClassName) {
                    case \DateTime::class:
                        $schema['properties'][$property] = [
                            'type'    => 'string',
                            'pattern' => '^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}\\+00:00$',
                        ];
                        break;
                    case Collection::class:
                        $schema['properties'][$property] = [
                            'type'  => 'array',
                            'items' => [
                                'type'       => 'object',
                                'properties' => [],
                            ],
                        ];
                        break;
                    default:
                        if (count($this->propertyInfoExtractor->getProperties($typeClassName, ['serializer_groups' => $groups]))) {
                            $schema['properties'][$property] = $this->getObjectJsonSchema($typeClassName);
                        } else {
                            $schema['properties'][$property] = [
                                'type'    => 'string',
                                'pattern' => sprintf('^%s$', str_ireplace('12345-abcde', '[\\w-]+', $this->getItemUri($typeClassName, '12345-abcde'))),
                            ];
                        }
                        break;
                }
            }
            $schema['properties'][$property]['description'] = $this->propertyInfoExtractor->getShortDescription($className, $property);
            if ('email' === $property) {
                $schema['properties'][$property]['pattern'] = '^[\\w\\.-]+@[\\w\\.-]+\\.[A-z]+$';
            }
        }

        return $schema;
    }
}
