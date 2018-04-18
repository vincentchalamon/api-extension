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
final class TransformerChain implements TransformerInterface
{
    /**
     * @var TransformerInterface[]
     */
    private $transformers;

    public function __construct(array $transformers)
    {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof TransformerAwareInterface) {
                $transformer->setTransformer($this);
            }
        }
        $this->transformers = $transformers;
    }

    public function toObject(array $mapping, $value)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($mapping, $value)) {
                return $transformer->toObject($mapping, $value);
            }
        }

        return $value;
    }

    public function toScalar(array $mapping, $value)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($mapping, $value)) {
                return $transformer->toScalar($mapping, $value);
            }
        }

        return $value;
    }

    public function supports(array $mapping, $value): bool
    {
        return true;
    }
}
