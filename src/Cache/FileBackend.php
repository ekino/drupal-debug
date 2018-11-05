<?php

namespace Ekino\Drupal\Debug\Cache;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Ekino\Drupal\Debug\Cache\Event\CacheNotFreshEvent;
use Ekino\Drupal\Debug\Cache\Event\FileBackendEvents;
use Ekino\Drupal\Debug\Exception\NotImplementedException;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FileBackend implements CacheBackendInterface
{
    /**
     * @var FileCache
     */
    private $fileCache;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @param FileCache $fileCache
     */
    public function __construct(FileCache $fileCache)
    {
        $this->fileCache = $fileCache;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cid, $allow_invalid = false)
    {
        if (!$this->fileCache->isFresh()) {
            $this->dispatch(FileBackendEvents::ON_CACHE_NOT_FRESH, new CacheNotFreshEvent($this->fileCache));

            return false;
        }

        $data = $this->fileCache->get();
        if (!is_array($data) || !array_key_exists($cid, $data['data'])) {
            return false;
        }

        $object = new \stdClass();
        $object->data = $data['data'][$cid];

        return $object;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function getMultiple(&$cids, $allow_invalid = false)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotSupportedException
     */
    public function set($cid, $data, $expire = Cache::PERMANENT, array $tags = [])
    {
        if (Cache::PERMANENT !== $expire) {
            throw new NotSupportedException();
        }

        if (!empty($tags)) {
            throw new NotSupportedException();
        }

        $this->fileCache->write(array(
            $cid => $data
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function setMultiple(array $items)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($cid)
    {
        $data = $this->fileCache->get();

        unset($data['data'][$cid]);

        $this->fileCache->write($data['data']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function deleteMultiple(array $cids)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll()
    {
        $this->fileCache->invalidate();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function invalidate($cid)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function invalidateMultiple(array $cids)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function invalidateAll()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function garbageCollection()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     */
    public function removeBin()
    {
        throw new NotImplementedException();
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $eventName
     * @param Event|null $event
     */
    private function dispatch($eventName, Event $event = null)
    {
        if ($this->eventDispatcher instanceof EventDispatcherInterface) {
            $this->eventDispatcher->dispatch($eventName, $event);
        }
    }
}
