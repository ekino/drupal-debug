<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Cache;

use Drupal\Core\Cache\Cache;
use Ekino\Drupal\Debug\Cache\Event\CacheNotFreshEvent;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Exception\NotImplementedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FileBackendTest extends TestCase
{
    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $eventDispatcher;

    /**
     * @var FileCache|MockObject
     */
    private $fileCache;

    /**
     * @var FileBackend
     */
    private $fileBackend;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->fileCache = $this->createMock(FileCache::class);

        $this->fileBackend = new FileBackend($this->fileCache);
    }

    public function testGetWithInvalidAllowInvalidArgument()
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('$allow_invalid with true value is not implemented.');

        $this->fileBackend->get('foo', true);
    }

    public function testGetWhenTheFileCacheIsNotFreshWithoutEventDispatcher()
    {
        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('isFresh')
            ->willReturn(false);

        $this->assertFalse($this->fileBackend->get('foo'));
    }

    public function testGetWhenTheFileCacheIsNotFreshWithEventDispatcher()
    {
        $this->fileCache
          ->expects($this->atLeastOnce())
          ->method('isFresh')
          ->willReturn(false);

        $this->fileBackend->setEventDispatcher($this->eventDispatcher);
        $this->eventDispatcher
          ->expects($this->atLeastOnce())
          ->method('dispatch')
          ->with('ekino.drupal.debug.file_backend.on_cache_not_fresh', new CacheNotFreshEvent($this->fileCache));

        $this->assertFalse($this->fileBackend->get('foo'));
    }

    /**
     * @dataProvider getWhenTheFileCacheIsFreshProvider
     */
    public function testGetWhenTheFileCacheIsFresh($expected, $data, $cid)
    {
        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('isFresh')
            ->willReturn(true);

        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($data);

        $this->assertEquals($expected, $this->fileBackend->get($cid));
    }

    public function getWhenTheFileCacheIsFreshProvider()
    {
        $object = new \stdClass();
        $object->data = 'bar';

        return array(
            array(
                false,
                array(
                    'ccc' => 'fcy',
                ),
                'foo',
            ),
            array(
                $object,
                array(
                    'foo' => 'bar',
                ),
                'foo',
            ),
        );
    }

    public function testGetMultiple()
    {
        $this->expectNotImplementedMethod('getMultiple');

        $cids = array('cid');
        $this->fileBackend->getMultiple($cids);
    }

    public function testSetWithInvalidExpireArgument()
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('$expire argument with "3600" value is not implemented.');

        $this->fileBackend->set('foo', 'bar', 3600);
    }

    public function testSetWithInvalidTagsArgument()
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('Non empty $tags argument is not implemented.');

        $this->fileBackend->set('foo', 'bar', Cache::PERMANENT, array('tag'));
    }

    public function testSet()
    {
        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('write')
            ->with(array(
                'foo' => 'bar',
            ));

        $this->fileBackend->set('foo', 'bar');
    }

    public function testSetMultiple()
    {
        $this->expectNotImplementedMethod('setMultiple');

        $this->fileBackend->setMultiple(array('item'));
    }

    public function testDeleteWhenThereIsNotDataInTheFileCache()
    {
        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn(false);

        $this->fileCache
            ->expects($this->never())
            ->method('write');

        $this->fileBackend->delete('foo');
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testDelete(array $expected, array $currentData, $cid)
    {
        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn(array(
                'data' => $currentData,
            ));

        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('write')
            ->with($expected);

        $this->fileBackend->delete($cid);
    }

    public function deleteProvider()
    {
        return array(
            array(
                array(
                    'ccc' => 'fcy',
                ),
                array(
                    'foo' => 'bar',
                    'ccc' => 'fcy',
                ),
              'foo',
            ),
            array(
                array(
                    'foo' => 'bar',
                ),
                array(
                    'foo' => 'bar',
                ),
                'ccc',
            ),
        );
    }

    public function testDeleteMultiple()
    {
        $this->expectNotImplementedMethod('deleteMultiple');

        $this->fileBackend->deleteMultiple(array('cid'));
    }

    public function testDeleteAll()
    {
        $this->fileCache
            ->expects($this->atLeastOnce())
            ->method('invalidate');

        $this->fileBackend->deleteAll();
    }

    public function testInvalidate()
    {
        $this->expectNotImplementedMethod('invalidate');

        $this->fileBackend->invalidate('cid');
    }

    public function testInvalidateMultiple()
    {
        $this->expectNotImplementedMethod('invalidateMultiple');

        $this->fileBackend->invalidateMultiple(array('cid'));
    }

    public function testInvalidateAll()
    {
        $this->expectNotImplementedMethod('invalidateAll');

        $this->fileBackend->invalidateAll();
    }

    public function testGarbageCollection()
    {
        $this->expectNotImplementedMethod('garbageCollection');

        $this->fileBackend->garbageCollection();
    }

    public function testRemoveBin()
    {
        $this->expectNotImplementedMethod('removeBin');

        $this->fileBackend->removeBin();
    }

    public function testSetEventDispatcher()
    {
        $this->fileBackend->setEventDispatcher($this->eventDispatcher);

        $this->assertAttributeSame($this->eventDispatcher, 'eventDispatcher', $this->fileBackend);
    }

    /**
     * @param string $method
     */
    private function expectNotImplementedMethod($method)
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage(\sprintf('The %s() method is not implemented.', $method));
    }
}
