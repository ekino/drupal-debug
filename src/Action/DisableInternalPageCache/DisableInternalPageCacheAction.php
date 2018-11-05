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

    /**
     * @param string $appRoot
     *
     * @return DisableInternalPageCacheAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
