<?php

declare(strict_types=1);

/*
 * This file is part of the ekino Drupal Debug project.
 *
 * (c) ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Drupal\Debug\Action\WatchContainerDefinitions;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;

class WatchContainerDefinitionsOptions extends AbstractFileBackendDependantOptions
{
    /**
     * {@inheritdoc}
     */
    protected static function canHaveModuleFileResourceMasks(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks(): array
    {
        return array(
            '%machine_name%.services.yml',
            'src/%camel_case_machine_name%ServiceProvider.php',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getCacheFileName(): string
    {
        return 'container_definition.php';
    }
}
