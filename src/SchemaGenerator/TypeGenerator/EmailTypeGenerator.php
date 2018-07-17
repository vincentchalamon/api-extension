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

namespace ApiExtension\SchemaGenerator\TypeGenerator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EmailTypeGenerator implements TypeGeneratorInterface, TypeGeneratorAwareInterface
{
    use TypeGeneratorAwareTrait;

    public function supports(array $mapping, array $context = []): bool
    {
        return 'email' === $mapping['fieldName'];
    }

    public function generate(array $mapping, array $context = []): array
    {
        $mapping['fieldName'] = null;

        return ['pattern' => '^[\\w\\.-]+@[\\w\\.-]+\\.[A-z]+$'] + $this->typeGenerator->generate($mapping, $context);
    }
}
