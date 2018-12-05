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

use ApiExtension\Exception\TypeGeneratorNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TypeGeneratorChain implements TypeGeneratorInterface
{
    /**
     * @var TypeGeneratorInterface[]
     */
    private $generators;

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if ($generator instanceof TypeGeneratorAwareInterface) {
                $generator->setTypeGenerator($this);
            }
        }
        $this->generators = $generators;
    }

    public function supports(array $context): bool
    {
        return true;
    }

    public function generate(array $context): array
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($context, $context)) {
                return $generator->generate($context, $context);
            }
        }

        throw new TypeGeneratorNotFoundException('No type generator found for mapping: '.print_r($context, true));
    }
}
