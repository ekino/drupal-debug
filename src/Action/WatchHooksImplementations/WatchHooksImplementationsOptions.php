<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\WatchHooksImplementations;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;

class WatchHooksImplementationsOptions extends AbstractFileBackendDependantOptions
{
    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks()
    {
        return array(
          '%machine_name%.module',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultThemeFileResourceMasks()
    {
        return array(
          '%machine_name%.theme',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultCacheFileName()
    {
        return 'hooks.php';
    }
}
