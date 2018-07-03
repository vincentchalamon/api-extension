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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ItemSchemaGenerator implements SchemaGeneratorInterface, SchemaGeneratorAwareInterface
{
    use SchemaGeneratorAwareTrait;

    /**
     * @var RouterInterface
     */
    private $router;

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return false === ($context['collection'] ?? false) && true === ($context['root'] ?? false);
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        unset($context['root'], $context['collection']);

        return array_merge_recursive($this->schemaGenerator->generate($reflectionClass, $context), [
            'properties' => [
                '@context' => [
                    'type' => 'string',
                    'pattern' => sprintf('^%s$', $this->router->generate('api_jsonld_context', ['shortName' => $reflectionClass->getShortName()])),
                ],
            ],
            'required' => ['@context'],
        ]);
    }
}
