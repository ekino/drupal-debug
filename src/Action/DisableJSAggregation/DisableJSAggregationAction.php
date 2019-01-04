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

namespace Ekino\Drupal\Debug\Action\DisableJSAggregation;

use Ekino\Drupal\Debug\Action\AbstractOverrideConfigAction;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;

class DisableJSAggregationAction extends AbstractOverrideConfigAction
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

    /**
     * {@inheritdoc}
     */
    protected function getOverrides(): array
    {
        return array(
          '[system.performance][js][preprocess]' => false,
        );
    }
}
