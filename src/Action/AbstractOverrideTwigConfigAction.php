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
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(self::TWIG_CONFIG_PARAMETER_NAME)) {
            throw new NotSupportedException(\sprintf('The "%s" parameter should already be set in the container builder.', self::TWIG_CONFIG_PARAMETER_NAME));
        }

        $container->setParameter(self::TWIG_CONFIG_PARAMETER_NAME, \array_merge($container->getParameter(self::TWIG_CONFIG_PARAMETER_NAME), $this->getOverrides()));
    }

    /**
     * @return array
     */
    abstract protected function getOverrides(): array;
}
