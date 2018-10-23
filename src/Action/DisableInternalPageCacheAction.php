<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Cache\NullBackendFactory;
use Drupal\Core\Site\Settings;

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
     * @return DisableRenderCacheAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
