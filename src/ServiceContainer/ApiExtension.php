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

namespace ApiExtension\ServiceContainer;

use ApiExtension\Populator\Guesser\GuesserChain;
use ApiExtension\SchemaGenerator\SchemaGeneratorChain;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ApiExtension implements ExtensionInterface
{
    use PriorityTaggedServiceTrait;

    public function getConfigKey()
    {
        return 'api';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
        if (null === $extensionManager->getExtension('behatch')
            || null === $extensionManager->getExtension('mink')
            || null === $extensionManager->getExtension('symfony2')
        ) {
            throw new \RuntimeException('ApiExtension requires Behatch\Extension, Behat\MinkExtension & Behat\Symfony2Extension.');
        }
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('default_locale')
                    ->info('The default locale used in Populator faker.')
                    ->cannotBeEmpty()
                    ->defaultValue('fr_FR')
                ->end()
                ->arrayNode('services')
                    ->isRequired()
                    ->children()
                        ->scalarNode('metadataFactory')
                            ->info('An instance of ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface')
                            ->cannotBeEmpty()
                            ->isRequired()
                        ->end()
                        ->scalarNode('iriConverter')
                            ->info('An instance of ApiPlatform\Core\Api\IriConverterInterface')
                            ->cannotBeEmpty()
                            ->isRequired()
                        ->end()
                        ->scalarNode('registry')
                            ->info('An instance of Symfony\Bridge\Doctrine\ManagerRegistry')
                            ->cannotBeEmpty()
                            ->isRequired()
                        ->end()
                        ->scalarNode('propertyInfo')
                            ->info('An instance of Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface')
                            ->cannotBeEmpty()
                            ->isRequired()
                        ->end()
                    ->end()
                ->end('services')
            ->end();
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('coop_tilleuls.api_extension.default_locale', $config['default_locale']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->getDefinition(SchemaGeneratorChain::class)->setArgument('$schemaGenerators', $this->findAndSortTaggedServices('coop_tilleuls.api_extension.schema_generator', $container));
        $container->getDefinition(GuesserChain::class)->setArgument('$guessers', $this->findAndSortTaggedServices('coop_tilleuls.api_extension.guesser', $container));
        $container->getDefinition(ApiConfigurator::class)->setArgument('$parameters', $config['services']);
    }

    public function process(ContainerBuilder $container)
    {
    }
}
