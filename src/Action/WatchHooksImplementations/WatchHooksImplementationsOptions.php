<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\WatchHooksImplementations;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;

class WatchHooksImplementationsOptions extends AbstractFileBackendDependantOptions
{
    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks(): array
    {
        return array(
            '%machine_name%.module',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultThemeFileResourceMasks(): array
    {
        return array(
            '%machine_name%.theme',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultCacheFileName(): string
    {
        return 'hooks.php';
    }
}
