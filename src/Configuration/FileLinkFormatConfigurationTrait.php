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

trait FileLinkFormatConfigurationTrait
{
    private static function addFileLinkFormatConfigurationNode(NodeBuilder $nodeBuilder, ?string $defaultFileLinkFormat): NodeBuilder
    {
        return $nodeBuilder
            ->scalarNode('file_link_format')
                ->defaultValue($defaultFileLinkFormat)
            ->end();
    }

    private static function addFileLinkFormatConfigurationNodeFromDefaultsConfiguration(NodeBuilder $nodeBuilder, DefaultsConfigurationModel $defaultsConfiguration): NodeBuilder
    {
        return self::addFileLinkFormatConfigurationNode($nodeBuilder, $defaultsConfiguration->getFileLinkFormat());
    }

    private static function getConfiguredFileLinkFormat(ActionConfiguration $actionConfiguration): ?string
    {
        $processedConfiguration = $actionConfiguration->getProcessedConfiguration();

        return $processedConfiguration['file_link_format'];
    }
}
