<?php

/*
 * This file is part of the ApiExtension package.
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

    public function supports(string $property, array $mapping, $value): bool
    {
        return true;
    }

    public function transform(string $property, array $mapping, $value)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($property, $mapping, $value)) {
                return $transformer->transform($property, $mapping, $value);
            }
        }

        return $value;
    }
}
