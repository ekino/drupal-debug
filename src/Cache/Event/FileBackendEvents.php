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

namespace Ekino\Drupal\Debug\Cache\Event;

class FileBackendEvents
{
    /**
     * @var string
     */
    const ON_CACHE_NOT_FRESH = 'ekino.drupal.debug.file_backend.on_cache_not_fresh';
}
