<?php

declare(strict_types=1);

/*
 * This file is part of the ekino Drupal Debug project.
 *
 * (c) ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Drupal\Debug\Configuration;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration as DefaultsConfigurationModel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SubstituteOriginalDrupalKernelConfiguration extends AbstractConfiguration
{
    /**
     * @var string
     */
    public const ROOT_KEY = 'substitute_original_drupal_kernel';

    private $defaultsConfiguration;

    public function __construct(DefaultsConfigurationModel $defaultsConfiguration)
    {
        $this->defaultsConfiguration = $defaultsConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getArrayNodeDefinition(TreeBuilder $treeBuilder): ArrayNodeDefinition
    {
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root(self::ROOT_KEY);

        $rootNode
            ->info("It is recommended to disable the original DrupalKernel substitution to run your tests.\nTo programmatically toggle it, use the two dedicated composer commands.")
            ->canBeDisabled()
            ->children()
                ->scalarNode('composer_autoload_file_path')
                    ->cannotBeEmpty()
                    ->defaultValue('vendor/autoload.php')
                ->end()
                ->scalarNode('cache_directory_path')
                    ->info('If not specified, it fall backs to the default cache directory path.')
                    ->defaultValue($this->defaultsConfiguration->getCacheDirectoryPath())
                ->end()
          ->end();

        return $rootNode;
    }
}
