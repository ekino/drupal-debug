<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('drupal-debug');

        $rootNode
            ->info('This is the drupal-debug configuration file.')
            ->children()
                ->append($this->getDefaultsConfigNode())
                ->append($this->getSubstituteOriginalDrupalKernelConfigNode())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getDefaultsConfigNode()
    {
        return (new ArrayNodeDefinition('defaults'))
            ->info('The defaults values are common values that are reused by different actions.')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('cache_directory')
                    ->cannotBeEmpty()
                    ->defaultValue('cache')
                ->end()
                ->arrayNode('logger')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('channel')
                            ->cannotBeEmpty()
                            ->defaultValue('drupal-debug')
                        ->end()
                        ->scalarNode('file_path')
                            ->cannotBeEmpty()
                            ->defaultValue('logs/drupal-debug.log')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('charset')
                    ->defaultNull()
                ->end()
                ->scalarNode('file_link_format')
                    ->defaultNull()
                ->end()
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getSubstituteOriginalDrupalKernelConfigNode()
    {
        return (new ArrayNodeDefinition('substitute_original_drupal_kernel'))
            ->info("It is recommended to disable the original DrupalKernel substitution to run your tests.\nTo programmatically toggle it, use the two dedicated composer commands.")
            ->canBeDisabled()
            ->children()
                ->scalarNode('composer_autoload_file_path')
                    ->cannotBeEmpty()
                    ->defaultValue('vendor/autoload.php')
                ->end()
                ->scalarNode('cache_directory')
                    ->info('If not specified, it fall backs to the default cache directory.')
                    ->defaultNull()
                ->end()
            ->end();
    }
}
