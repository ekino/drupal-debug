<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        if (!$container->hasParameter(self::TWIG_CONFIG_PARAMETER_NAME)) {
            throw new NotSupportedException(\sprintf('The "%s" parameter should already be set in the container builder.', self::TWIG_CONFIG_PARAMETER_NAME));
        }

        $container->setParameter(self::TWIG_CONFIG_PARAMETER_NAME, \array_merge($container->getParameter(self::TWIG_CONFIG_PARAMETER_NAME), $this->getOverride()));
    }

    /**
     * @return array
     */
    abstract protected function getOverride();
}
