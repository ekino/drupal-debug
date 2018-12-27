<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Cache\Event;

use Ekino\Drupal\Debug\Cache\Event\CacheNotFreshEvent;
use Ekino\Drupal\Debug\Cache\FileCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheNotFreshEventTest extends TestCase
{
    /**
     * @var FileCache|MockObject
     */
    private $fileCache;

    /**
     * @var CacheNotFreshEvent
     */
    private $cacheNotFreshEvent;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->fileCache = $this->createMock(FileCache::class);

        $this->cacheNotFreshEvent = new CacheNotFreshEvent($this->fileCache);
    }

    public function testGetFileCache()
    {
        $this->assertSame($this->fileCache, $this->cacheNotFreshEvent->getFileCache());
    }
}
