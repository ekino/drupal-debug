<?php

namespace Ekino\Drupal\Debug\Action\DisableRenderCache;

use Ekino\Drupal\Debug\Action\AbstractDisableDrupalCacheAction;

class DisableRenderCacheAction extends AbstractDisableDrupalCacheAction
{
    /**
     * {@inheritdoc}
     */
    protected function getBin()
    {
        return 'render';
    }
}
