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

namespace Ekino\Drupal\Debug\Kernel\Event;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractWithContainerAndEnabledExtensionsEvent extends AbstractWithEnabledExtensionsEvent
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param string[]           $enabledModules
     * @param string[]           $enabledThemes
     */
    public function __construct(ContainerInterface $container, array $enabledModules, array $enabledThemes)
    {
        parent::__construct($enabledModules, $enabledThemes);

        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
