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

namespace ApiExtension\SchemaGenerator\TypeGenerator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EmailTypeGenerator implements TypeGeneratorInterface, TypeGeneratorAwareInterface
{
    use TypeGeneratorAwareTrait;

    public function supports(string $property, array $mapping, array $context = []): bool
    {
        return 'email' === $property;
    }

    public function generate(string $property, array $mapping, array $context = []): array
    {
        return ['pattern' => '^[\\w\\.-]+@[\\w\\.-]+\\.[A-z]+$'] + $this->typeGenerator->generate('', $mapping, $context);
    }
}
