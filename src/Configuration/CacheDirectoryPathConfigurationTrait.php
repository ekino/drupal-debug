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

use Ekino\Drupal\Debug\Configuration\Model\ActionConfiguration;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration as DefaultsConfigurationModel;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

trait CacheDirectoryPathConfigurationTrait
{
    private static function addCacheDirectoryPathConfigurationNode(NodeBuilder $nodeBuilder, ?string $defaultCacheDirectoryPath): NodeBuilder
    {
        return $nodeBuilder
            ->scalarNode('cache_directory_path')
                ->cannotBeEmpty()
                ->defaultValue($defaultCacheDirectoryPath)
            ->end();
    }

    private static function addCacheDirectoryPathConfigurationNodeFromDefaultsConfiguration(NodeBuilder $nodeBuilder, DefaultsConfigurationModel $defaultsConfiguration): NodeBuilder
    {
        return self::addCacheDirectoryPathConfigurationNode($nodeBuilder, $defaultsConfiguration->getCacheDirectoryPath());
    }

    private static function getConfiguredCacheDirectoryPath(ActionConfiguration $actionConfiguration): string
    {
        $processedConfiguration = $actionConfiguration->getProcessedConfiguration();

        return $processedConfiguration['cache_directory_path'];
    }
}
