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

use Ekino\Drupal\Debug\ActionMetadata\Model\ActionMetadata;
use Ekino\Drupal\Debug\ActionMetadata\Model\ActionWithOptionsMetadata;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration as DefaultsConfigurationModel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ActionsConfiguration extends AbstractConfiguration
{
    public const ROOT_KEY = 'actions';

    /**
     * @var ActionMetadata[]
     */
    private $actionsMetadata;

    private $defaultsConfiguration;

    /**
     * @param ActionMetadata[]           $actionsMetadata
     * @param DefaultsConfigurationModel $defaultsConfiguration
     */
    public function __construct(array $actionsMetadata, DefaultsConfigurationModel $defaultsConfiguration)
    {
        $this->actionsMetadata = $actionsMetadata;
        $this->defaultsConfiguration = $defaultsConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getArrayNodeDefinition(TreeBuilder $treeBuilder): ArrayNodeDefinition
    {
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root(self::ROOT_KEY);
        $nodeBuilder = $rootNode
            ->addDefaultsIfNotSet()
            ->children();

        foreach ($this->actionsMetadata as $actionMetadata) {
            $childrenNodeBuilder = $nodeBuilder
                ->arrayNode($actionMetadata->getShortName())
                    ->canBeDisabled()
                    ->children();

            if ($actionMetadata instanceof ActionWithOptionsMetadata) {
                $actionMetadata->getOptionsClass()::addConfiguration($childrenNodeBuilder, $this->defaultsConfiguration);
            }

            $childrenNodeBuilder
                ->end();
        }

        $rootNode
            ->end();

        return $rootNode;
    }
}
