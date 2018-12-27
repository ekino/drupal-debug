<?php

declare(strict_types=1);

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
}
