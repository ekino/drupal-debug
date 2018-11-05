<?php

namespace Ekino\Drupal\Debug\Action\DisableDynamicPageCache;

use Ekino\Drupal\Debug\Action\AbstractDisableDrupalCacheAction;

class DisableDynamicPageCacheAction extends AbstractDisableDrupalCacheAction
{
    /**
     * {@inheritdoc}
     */
    protected function getBin()
    {
        return 'dynamic_page_cache';
    }

    /**
     * @param string $appRoot
     *
     * @return DisableDynamicPageCacheAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
