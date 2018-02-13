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

namespace ApiExtension\Transformer;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
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
