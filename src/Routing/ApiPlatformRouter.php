<?php

declare(strict_types=1);

namespace ApiExtension\Routing;

use ApiExtension\Routing\RouterInterface;
use ApiPlatform\Core\Api\IriConverterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ApiPlatformRouter implements RouterInterface
{
    private $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function getCollectionUri(\ReflectionClass $reflectionClass): string
    {
        return $this->iriConverter->getIriFromResourceClass($reflectionClass->name);
    }

    public function getItemUri(\ReflectionClass $reflectionClass, array $identifiers): string
    {
        return $this->iriConverter->getItemIriFromResourceClass($reflectionClass->name, $identifiers);
    }

    public function supports(\ReflectionClass $reflectionClass): bool
    {
        return true;
    }
}
