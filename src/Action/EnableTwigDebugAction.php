<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

class EnableTwigDebugAction extends AbstractOverrideTwigConfigAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOverride()
    {
        return array(
            'debug' => true
        );
    }

    /**
     * @param string $appRoot
     *
     * @return EnableTwigDebugAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
