<?php

declare(strict_types=1);

namespace Drupal\use_custom_service;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

class UseCustomServiceServiceProvider implements ServiceModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function alter(ContainerBuilder $container)
    {
        $container->hasDefinition('use_custom_service.service.foo') ?
            $container->getDefinition('use_custom_service.service.foo')->replaceArgument(0, '%message%') :
            $container->setParameter('use_custom_service.parameter.bar', '%message%');
    }
}
