<?php

declare(strict_types=1);

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
            'debug' => true,
        );
    }
}
