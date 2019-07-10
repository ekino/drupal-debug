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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

abstract class AbstractConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    final public function getConfigTreeBuilder(): TreeBuilder
    {
        $this->getArrayNodeDefinition($treeBuilder = new TreeBuilder());

        return $treeBuilder;
    }

    abstract public function getArrayNodeDefinition(TreeBuilder $treeBuilder);
}
