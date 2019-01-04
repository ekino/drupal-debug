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
    public function getFileCache(): FileCache
    {
        return $this->fileCache;
    }
}
