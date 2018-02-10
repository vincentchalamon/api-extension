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
trait TypeGeneratorAwareTrait
{
    /**
     * @var TypeGeneratorInterface
     */
    private $typeGenerator;

    public function setTypeGenerator(TypeGeneratorInterface $typeGenerator): void
    {
        $this->typeGenerator = $typeGenerator;
    }
}
