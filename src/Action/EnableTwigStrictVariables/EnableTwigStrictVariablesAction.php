<?php

namespace Ekino\Drupal\Debug\Action\EnableTwigStrictVariables;

use Ekino\Drupal\Debug\Action\AbstractOverrideTwigConfigAction;

class EnableTwigStrictVariablesAction extends AbstractOverrideTwigConfigAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOverride()
    {
        return array(
            'strict_variables' => true
        );
    }

    /**
     * @param string $appRoot
     *
     * @return EnableTwigStrictVariablesAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
