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

namespace Ekino\Drupal\Debug\Action\WatchRoutingDefinitions;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;

class WatchRoutingDefinitionsOptions extends AbstractFileBackendDependantOptions
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
            '%machine_name%.routing.yml',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getCacheFileName(): string
    {
        return 'routing.meta';
    }
}
