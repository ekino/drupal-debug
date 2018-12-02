<?php

namespace Ekino\Drupal\Debug\Action\DisableInternalPageCache;

use Ekino\Drupal\Debug\Action\AbstractDisableDrupalCacheAction;

class DisableInternalPageCacheAction extends AbstractDisableDrupalCacheAction
{
    /**
     * {@inheritdoc}
     */
    protected function getBin()
    {
        return 'page';
    }
}
