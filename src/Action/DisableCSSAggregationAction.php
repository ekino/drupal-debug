<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Cache\NullBackendFactory;
use Drupal\Core\Site\Settings;
use Ekino\Drupal\Debug\Event\DebugKernelEvents;

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
            '[system.performance][css][preprocess]' => false
        );
    }

    /**
     * @param string $appRoot
     *
     * @return DisableCSSAggregationAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
