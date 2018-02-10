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

namespace ApiExtension\SchemaGenerator\TypeGenerator;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
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
