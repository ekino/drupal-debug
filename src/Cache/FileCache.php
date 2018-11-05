<?php

namespace Ekino\Drupal\Debug\Cache;

use Ekino\Drupal\Debug\Resource\ResourcesFreshnessChecker;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FileCache
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var ResourcesFreshnessChecker
     */
    private $resourcesFreshnessChecker;

    /**
     * @param string $filePath
     * @param SelfCheckingResourceInterface[] $resources
     */
    public function __construct($filePath, array $resources)
    {
        $this->filePath = $filePath;

        $this->resourcesFreshnessChecker = new ResourcesFreshnessChecker(sprintf('%s.meta', $filePath), $resources);
    }

    /**
     * @return bool
     */
    public function isFresh()
    {
        return $this->resourcesFreshnessChecker->isFresh();
    }

    /**
     * @return array|false
     */
    public function get()
    {
        if (!is_file($this->filePath)) {
            return false;
        }

        $data = require $this->filePath;
        if (!is_array($data) || !array_key_exists('data', $data)) {
            throw new \LogicException();
        }

        return $data;
    }

    /**
     * @param array $data
     */
    public function write(array $data)
    {
        $currentData = $this->get();
        if (is_array($currentData)) {
            $data = array_merge($currentData['data'], $data);
        }

        $umask = umask();
        $filesystem = new Filesystem();
        
        $filesystem->dumpFile($this->filePath, '<?php return ' . var_export(array(
            'date' => date(DATE_ATOM),
            'data' => $data,
        ), true) . ';');
        
        try {
            $filesystem->chmod($this->filePath, 0666, $umask);
        } catch (IOException $e) {
            // discard chmod failure (some filesystem may not support it)
        }

        if (function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
            @opcache_invalidate($this->filePath, true);
        }

        $this->resourcesFreshnessChecker->commit();
    }

    public function invalidate()
    {
        unlink($this->filePath);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return SelfCheckingResourceInterface[]
     */
    public function getCurrentResources()
    {
        return $this->resourcesFreshnessChecker->getCurrentResources();
    }
}
