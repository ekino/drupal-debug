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

trait CharsetConfigurationTrait
{
    private static function addCharsetConfigurationNode(NodeBuilder $nodeBuilder, ?string $defaultCharset): NodeBuilder
    {
        return $nodeBuilder
            ->scalarNode('charset')
                ->defaultValue($defaultCharset)
            ->end();
    }

    private static function addCharsetConfigurationNodeFromDefaultsConfiguration(NodeBuilder $nodeBuilder, DefaultsConfigurationModel $defaultsConfiguration): NodeBuilder
    {
        return self::addCharsetConfigurationNode($nodeBuilder, $defaultsConfiguration->getCharset());
    }

    private static function getConfiguredCharset(ActionConfiguration $actionConfiguration): ?string
    {
        $processedConfiguration = $actionConfiguration->getProcessedConfiguration();

        return $processedConfiguration['charset'];
    }
}
