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

namespace ApiExtension\Context\Argument;

use Behat\Behat\Context\Argument\ArgumentResolver as ArgumentResolverInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ArgumentResolver implements ArgumentResolverInterface
{
    private $dependencies;

    public function __construct(/* ... */)
    {
        $this->dependencies = func_get_args();
    }

    public function resolveArguments(\ReflectionClass $classReflection, array $arguments): array
    {
        if (null !== ($constructor = $classReflection->getConstructor())) {
            foreach ($constructor->getParameters() as $parameter) {
                if (null !== $parameter->getClass()) {
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
