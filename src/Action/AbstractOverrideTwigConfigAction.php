<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractOverrideTwigConfigAction implements CompilerPassActionInterface
{
    /**
     * @var string
     */
    const TWIG_CONFIG_PARAMETER_NAME = 'twig.config';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = $this->getOverride();
        if ($container->hasParameter(self::TWIG_CONFIG_PARAMETER_NAME)) {
            $config = array_merge($container->getParameter(self::TWIG_CONFIG_PARAMETER_NAME), $config);
        }

        $container->setParameter(self::TWIG_CONFIG_PARAMETER_NAME, $config);
    }

    /**
     * @return array
     */
    abstract protected function getOverride();
}
