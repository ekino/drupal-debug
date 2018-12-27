<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\DisableCSSAggregation;

use Ekino\Drupal\Debug\Action\AbstractOverrideConfigAction;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;

class DisableCSSAggregationAction extends AbstractOverrideConfigAction
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'process',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOverrides()
    {
        return array(
            '[system.performance][css][preprocess]' => false,
        );
    }
}
