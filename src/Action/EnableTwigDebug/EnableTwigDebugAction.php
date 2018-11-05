<?php

namespace Ekino\Drupal\Debug\Action\EnableTwigDebug;

use Ekino\Drupal\Debug\Action\AbstractOverrideTwigConfigAction;

class EnableTwigDebugAction extends AbstractOverrideTwigConfigAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOverride()
    {
        return array(
            'debug' => true
        );
    }

    /**
     * @param string $appRoot
     *
     * @return EnableTwigDebugAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
