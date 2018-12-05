<?php

declare(strict_types=1);

namespace ApiExtension\ClassRepository;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ClassRepositoryInterface
{
    /**
     * @throws ClassRepositoryNotFoundException
     */
    public function getReflectionClass(string $alias): \ReflectionClass;
}
