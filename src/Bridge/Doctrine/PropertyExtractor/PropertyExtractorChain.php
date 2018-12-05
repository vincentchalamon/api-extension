<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\PropertyExtractor;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class PropertyExtractorChain implements PropertyExtractorInterface
{
    /**
     * @var PropertyExtractorInterface[]
     */
    private $objects;

    public function __construct(array $objects)
    {
        $this->objects = $objects;
    }

    public function getProperties(\ReflectionClass $reflectionClass, array $context = []): array
    {
        foreach ($this->objects as $object) {
            if ($object->supports($reflectionClass, $context)) {
                return $object->getProperties($reflectionClass, $context);
            }
        }

        throw new PropertyExtractorNotFoundException();
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return true;
    }
}
