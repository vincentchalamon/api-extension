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

namespace ApiExtension\Context\Argument;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Behat\Behat\Context\Argument\ArgumentResolver as ArgumentResolverInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ArgumentResolver implements ArgumentResolverInterface
{
    private $dependencies;

    public function __construct(/* ... */)
    {
        $this->dependencies = func_get_args();
    }

    public function setMetadataFactory(ResourceMetadataFactoryInterface $metadataFactory)
    {
        $this->dependencies[] = $metadataFactory;
    }

    public function setPropertyInfo(PropertyInfoExtractorInterface $propertyInfo)
    {
        $this->dependencies[] = $propertyInfo;
    }

    public function setRegistry(ManagerRegistry $registry)
    {
        $this->dependencies[] = $registry;
    }

    public function setIriConverter(IriConverterInterface $iriConverter)
    {
        $this->dependencies[] = $iriConverter;
    }

    public function resolveArguments(\ReflectionClass $classReflection, array $arguments): array
    {
        if (null !== ($constructor = $classReflection->getConstructor())) {
            foreach ($constructor->getParameters() as $parameter) {
                if (null !== $parameter->getClass() && !isset($arguments[$parameter->getName()])) {
                    foreach ($this->dependencies as $dependency) {
                        if (is_a($dependency, $parameter->getClass()->name, false)) {
                            $arguments[$parameter->name] = $dependency;
                        }
                    }
                }
            }
        }

        return $arguments;
    }
}
