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
    protected function setUp(): void
    {
        $this->fileCache = $this->createMock(FileCache::class);

        $this->cacheNotFreshEvent = new CacheNotFreshEvent($this->fileCache);
    }

    public function testGetFileCache(): void
    {
        $this->assertSame($this->fileCache, $this->cacheNotFreshEvent->getFileCache());
    }
}
