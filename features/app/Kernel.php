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

namespace ApiExtension\App;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as AbstractKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Test purpose micro-kernel.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Kernel extends AbstractKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        return __DIR__.'/cache/'.$this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return __DIR__.'/logs/'.$this->getEnvironment();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function registerBundles(): array
    {
        return [
            new ApiPlatformBundle(),
            new FrameworkBundle(),
            new DoctrineBundle(),
            new TwigBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routes->import('.', null, 'api_platform');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'path' => '%kernel.cache_dir%/db.sqlite',
                'charset' => 'UTF8',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
                'auto_mapping' => true,
                'mappings' => [
                    'App' => [
                        'is_bundle' => false,
                        'type' => 'annotation',
                        'dir' => __DIR__ . '/Entity',
                        'prefix' => 'ApiExtension\App\Entity',
                        'alias' => 'App',
                    ],
                ],
            ],
        ]);

        $c->loadFromExtension('framework', [
            'secret' => 'ApiExtension',
            'test' => null,
        ]);

        $c->loadFromExtension('api_platform', [
            'title' => 'ApiExtension test',
            'version' => '1.2.3',
            'mapping' => [
                'paths' => [__DIR__ . '/Entity'],
            ],
        ]);

        $loader->load(__DIR__ . '/services.yaml');
    }
}
