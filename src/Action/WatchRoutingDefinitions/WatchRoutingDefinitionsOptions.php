<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\WatchRoutingDefinitions;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;

class WatchRoutingDefinitionsOptions extends AbstractFileBackendDependantOptions
{
    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks(): array
    {
        return array(
            '%machine_name%.routing.yml',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultCacheFileName(): string
    {
        return 'routing.meta';
    }
}
