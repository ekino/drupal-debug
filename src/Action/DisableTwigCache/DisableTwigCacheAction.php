<?php

namespace Ekino\Drupal\Debug\Action\DisableTwigCache;

use Ekino\Drupal\Debug\Action\AbstractOverrideTwigConfigAction;

class DisableTwigCacheAction extends AbstractOverrideTwigConfigAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOverride()
    {
        return array(
            'cache' => false
        );
    }

    /**
     * @param string $appRoot
     *
     * @return DisableTwigCacheAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
