<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Cache\Event;

use Ekino\Drupal\Debug\Cache\FileCache;
use Symfony\Component\EventDispatcher\Event;

class CacheNotFreshEvent extends Event
{
    /**
     * @var FileCache
     */
    private $fileCache;

    /**
     * @param FileCache $fileCache
     */
    public function __construct(FileCache $fileCache)
    {
        $this->fileCache = $fileCache;
    }

    /**
     * @return FileCache
     */
    public function getFileCache()
    {
        return $this->fileCache;
    }
}
