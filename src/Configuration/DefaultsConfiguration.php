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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DefaultsConfiguration extends AbstractConfiguration
{
    use CacheDirectoryPathConfigurationTrait;
    use LoggerConfigurationTrait;
    use CharsetConfigurationTrait;
    use FileLinkFormatConfigurationTrait;

    /**
     * @var string
     */
    public const ROOT_KEY = 'defaults';

    /**
     * {@inheritdoc}
     */
    public function getArrayNodeDefinition(TreeBuilder $treeBuilder): ArrayNodeDefinition
    {
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root(self::ROOT_KEY);

        $nodeBuilder = $rootNode
            ->info('The defaults values are common values that are reused by different actions.')
            ->addDefaultsIfNotSet()
            ->children();

        self::addCacheDirectoryPathConfigurationNode($nodeBuilder, 'cache');
        self::addLoggerConfigurationNode($nodeBuilder, 'drupal-debug', 'logs/drupal-debug.log');
        self::addCharsetConfigurationNode($nodeBuilder, null);
        self::addFileLinkFormatConfigurationNode($nodeBuilder, null);

        $nodeBuilder->end();

        return $rootNode;
    }
}
