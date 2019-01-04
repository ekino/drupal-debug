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

namespace Drupal\use_custom_service;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

class UseCustomServiceServiceProvider implements ServiceModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function alter(ContainerBuilder $container): void
    {
        $container->hasDefinition('use_custom_service.service.foo') ?
            $container->getDefinition('use_custom_service.service.foo')->replaceArgument(0, '%message%') :
            $container->setParameter('use_custom_service.parameter.bar', '%message%');
    }
}
