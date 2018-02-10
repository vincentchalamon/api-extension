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

namespace ApiExtension\SchemaGenerator;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface SchemaGeneratorInterface
{
    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool;

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array;
}
