<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Option;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;

interface OptionsInterface
{
    /**
     * @param string                $appRoot
     * @param DefaultsConfiguration $defaultsConfiguration
     *
     * @return self
     */
    public static function getDefault($appRoot, DefaultsConfiguration $defaultsConfiguration);
}
