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

namespace ApiExtension\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ApiConfigurator
{
    /**
     * @var ContainerInterface
     */
    private $container;
    private $parameters;

    public function __construct(KernelInterface $kernel, array $parameters)
    {
        $this->container = $kernel->getContainer();
        $this->parameters = $parameters;
    }

    public function configure($service)
    {
        foreach ($this->parameters as $name => $value) {
            $methodName = 'set'.ucfirst($name);
            if (!method_exists($service, $methodName)) {
                continue;
            }
            if (is_scalar($value)) {
                if (preg_match('/^%(.*)%$/', $value, $matches)) {
                    $value = $this->container->getParameter($matches[1]);
                } else {
                    $value = $this->container->get('@' === substr($value, 0, 1) ? substr($value, 1) : $value);
                }
            }
            call_user_func([$service, $methodName], $value);
        }
    }
}
