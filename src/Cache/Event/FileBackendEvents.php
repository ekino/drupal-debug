<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Cache\Event;

class FileBackendEvents
{
    /**
     * @var string
     */
    const ON_CACHE_NOT_FRESH = 'ekino.drupal.debug.file_backend.on_cache_not_fresh';
}
