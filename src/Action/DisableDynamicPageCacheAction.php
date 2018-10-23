<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Cache\NullBackendFactory;
use Drupal\Core\Site\Settings;

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
     * @return DisableRenderCacheAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
