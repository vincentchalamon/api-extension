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

namespace ApiExtension\ServiceContainer;

use ApiExtension\Populator\Guesser\GuesserChain;
use ApiExtension\SchemaGenerator\SchemaGeneratorChain;
use ApiExtension\SchemaGenerator\TypeGenerator\TypeGeneratorChain;
use ApiExtension\Transformer\TransformerChain;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Faker\Generator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
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
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('metadataFactory')
                            ->info('An instance of ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface')
                            ->cannotBeEmpty()
                            ->defaultValue('@api_platform.metadata.resource.metadata_factory.annotation')
                        ->end()
                        ->scalarNode('iriConverter')
                            ->info('An instance of ApiPlatform\Core\Api\IriConverterInterface')
                            ->cannotBeEmpty()
                            ->defaultValue('@api_platform.iri_converter')
                        ->end()
                        ->scalarNode('registry')
                            ->info('An instance of Doctrine\Common\Persistence\ManagerRegistry')
                            ->cannotBeEmpty()
                            ->defaultValue('@doctrine')
                        ->end()
                        ->scalarNode('propertyInfo')
                            ->info('An instance of Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface')
                            ->cannotBeEmpty()
                            ->defaultValue('@property_info')
                        ->end()
                        ->scalarNode('annotationReader')
                            ->info('An instance of Doctrine\Common\Annotations\Reader')
                            ->cannotBeEmpty()
                            ->defaultValue('@annotation_reader')
                        ->end()
                        ->scalarNode('router')
                            ->info('An instance of Symfony\Component\Routing\RouterInterface')
                            ->cannotBeEmpty()
                            ->defaultValue('@router')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('providers')
                    ->info('A list of Faker providers')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('guessers')
                    ->info('A list of Populators guessers')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('transformers')
                    ->info('A list of transformers')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('coop_tilleuls.api_extension.default_locale', $config['default_locale']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        foreach (['guessers' => 'coop_tilleuls.api_extension.guesser', 'transformers' => 'coop_tilleuls.api_extension.transformer'] as $configKey => $tag) {
            foreach ($config[$configKey] as $class) {
                $container->setDefinition($class, new Definition($class, [new Reference(Generator::class)]))->addTag($tag);
            }
        }

        $container->getDefinition(SchemaGeneratorChain::class)->setArgument('$generators', $this->findAndSortTaggedServices('coop_tilleuls.api_extension.schema_generator', $container));
        $container->getDefinition(TypeGeneratorChain::class)->setArgument('$generators', $this->findAndSortTaggedServices('coop_tilleuls.api_extension.schema_generator.type', $container));
        $container->getDefinition(GuesserChain::class)->setArgument('$guessers', $this->findAndSortTaggedServices('coop_tilleuls.api_extension.guesser', $container));
        $container->getDefinition(TransformerChain::class)->setArgument('$transformers', $this->findAndSortTaggedServices('coop_tilleuls.api_extension.transformer', $container));
        $container->getDefinition(ApiConfigurator::class)->setArgument('$parameters', $config['services']);

        foreach ($config['providers'] as $class) {
            $container->setDefinition($class, new Definition($class, [new Reference(Generator::class)]));
            $container->getDefinition(Generator::class)->addMethodCall('addProvider', [new Reference($class)]);
        }
    }

    public function process(ContainerBuilder $container)
    {
    }
}
