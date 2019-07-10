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

namespace Ekino\Drupal\Debug\Action\WatchModulesHooksImplementations;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantOptions;

class WatchModulesHooksImplementationsOptions extends AbstractFileBackendDependantOptions
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
            '%machine_name%.module',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getCacheFileName(): string
    {
        return 'modules_hooks.php';
    }
}
