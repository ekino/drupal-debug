<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\WatchContainerDefinitions;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;

class WatchContainerDefinitionsOptions extends AbstractFileBackendDependantOptions
{
    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks()
    {
        return array(
          '%machine_name%.services.yml',
          'src/%camel_case_machine_name%ServiceProvider.php',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultCacheFileName()
    {
        return 'container_definition.php';
    }
}
