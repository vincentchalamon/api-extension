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

namespace ApiExtension\Transformer;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
trait TransformerAwareTrait
{
    /**
     * @var TransformerInterface
     */
    private $transformer;

    public function setTransformer(TransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
    }
}
