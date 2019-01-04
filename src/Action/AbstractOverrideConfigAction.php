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

use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractOverrideConfigAction implements EventSubscriberActionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'process',
        );
    }

    public function process(): void
    {
        global $config;

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($this->getOverrides() as $propertyPath => $value) {
            $propertyAccessor->setValue($config, $propertyPath, $value);
        }
    }

    /**
     * @return array
     */
    abstract protected function getOverrides(): array;
}
