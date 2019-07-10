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
use Ekino\Drupal\Debug\Logger\LoggerStack;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

trait LoggerConfigurationTrait
{
    private static function addLoggerConfigurationNode(NodeBuilder $nodeBuilder, string $defaultChannel, string $defaultFilePath): NodeBuilder
    {
        return $nodeBuilder
            ->arrayNode('logger')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('channel')
                        ->cannotBeEmpty()
                        ->defaultValue($defaultChannel)
                    ->end()
                    ->scalarNode('file_path')
                        ->cannotBeEmpty()
                        ->defaultValue($defaultFilePath)
                    ->end()
                ->end()
            ->end();
    }

    private static function addLoggerConfigurationNodeFromDefaultsConfiguration(NodeBuilder $nodeBuilder, DefaultsConfigurationModel $defaultsConfiguration): NodeBuilder
    {
        $defaultLoggerConfiguration = $defaultsConfiguration->getLogger();

        return self::addLoggerConfigurationNode($nodeBuilder, $defaultLoggerConfiguration['channel'], $defaultLoggerConfiguration['file_path']);
    }

    private static function getConfiguredLogger(ActionConfiguration $actionConfiguration): ?Logger
    {
        $processedConfiguration = $actionConfiguration->getProcessedConfiguration();
        if (!$processedConfiguration['logger']['enabled']) {
            return null;
        }

        return LoggerStack::getInstance($processedConfiguration['logger']['channel'], $processedConfiguration['logger']['file_path']);
    }
}
