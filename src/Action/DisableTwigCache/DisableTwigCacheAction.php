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
            'cache' => false,
        );
    }
}
