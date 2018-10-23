<?php

namespace Ekino\Drupal\Debug\Helper;

use Ekino\Drupal\Debug\Action\EnhanceClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnhanceContainerAction;
use Ekino\Drupal\Debug\Action\EnhanceDumpAction;
use Ekino\Drupal\Debug\Action\EnhanceExceptionPageAction;
use Symfony\Component\Config\ConfigCache;

class ConfigCacheHelper
{
    /**
     * @var ConfigCache
     */
    private $configCache;

    /**
     * @var ResourceInterface[]
     */
    private $resources;

    /**
     * @param string $cacheFilePath
     * @param ResourceInterface[] $resources
     */
    public function __construct($cacheFilePath, array $resources)
    {
        $this->resources = $resources;

        $cacheFilePathInfo = pathinfo($cacheFilePath);
        $cacheFilePath = sprintf('%s/%s.%s', $cacheFilePathInfo['dirname'], $cacheFilePathInfo['filename'], crc32(serialize($this->resources)));

        if (isset($cacheFilePathInfo['extension'])) {
            $cacheFilePath .= sprintf('.%s', $cacheFilePathInfo['extension']);
        }

        $this->configCache = new ConfigCache($cacheFilePath, true);
    }

    /**
     * @return bool
     */
    public function isFresh()
    {
        return $this->configCache->isFresh();
    }

    /**
     * @param array $data
     */
    public function write(array $dataToWrite)
    {
        $currentData = $this->get();
        if (is_array($currentData)) {
            $dataToWrite = array_merge($currentData['data'], $dataToWrite);
        }

        $this->configCache->write('<?php return ' . var_export(array(
          'date' => date(DATE_ATOM),
          'data' => $dataToWrite,
        ), true) . ';', $this->resources);
    }

    /**
     * @return array|false
     */
    public function get()
    {
        if (!is_file($this->configCache->getPath())) {
            return false;
        }

        $data = require $this->configCache->getPath();
        if (!is_array($data) || !array_key_exists('data', $data)) {
            throw new \LogicException();
        }

        return $data;
    }

    public function invalidate()
    {
        unlink($this->configCache->getPath());
    }
}
