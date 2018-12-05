<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\PropertyExtractor;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface PropertyExtractorInterface
{
    public function getProperties(\ReflectionClass $reflectionClass, array $context = []): array;

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool;
}
