<?php

namespace Ekino\Drupal\Debug\Cache;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Ekino\Drupal\Debug\Helper\ConfigCacheHelper;
use Ekino\Drupal\Debug\NotImplementedException;
use Ekino\Drupal\Debug\NotSupportedException;

class FileBackend implements CacheBackendInterface
{
    /**
     * @var ConfigCacheHelper
     */
    private $configCacheHelper;

    /**
     * @param string $cacheFilePath
     * @param ResourceInterface[] $resources
     */
    public function __construct($cacheFilePath, array $resources)
    {
        $this->configCacheHelper = new ConfigCacheHelper($cacheFilePath, $resources);
    }

    /**
     * {@inheritdoc}
     */
    public function get($cid, $allow_invalid = false)
    {
        if (!$this->configCacheHelper->isFresh()) {
            return false;
        }

        $data = $this->configCacheHelper->get();
        if (!is_array($data) || !array_key_exists($cid, $data['data'])) {
            return false;
        }

        $o = new \stdClass();
        $o->data = $data['data'][$cid];

        return $o;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(&$cids, $allow_invalid = false)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function set($cid, $data, $expire = Cache::PERMANENT, array $tags = [])
    {
        if (Cache::PERMANENT !== $expire) {
            throw new NotSupportedException();
        }

        if (!empty($tags)) {
            throw new NotSupportedException();
        }

        $this->configCacheHelper->write(array(
            $cid => $data
        ));
    }

    /**
     * {@inheritdoc}
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
        $data = $this->configCacheHelper->get();
        if (!is_array($data)) {
            return;
        }

        unset($data['data'][$cid]);

        $this->configCacheHelper->write($data['data']);
    }

    /**
     * {@inheritdoc}
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
        $this->configCacheHelper->invalidate();
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($cid)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateMultiple(array $cids)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateAll()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function garbageCollection()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBin()
    {
        throw new NotImplementedException();
    }
}
