<?php

namespace Ekino\Drupal\Debug\Resource;

use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class ResourcesFreshnessChecker
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var SelfCheckingResourceInterface[]
     */
    private $resources;

    /**
     * Lazy loaded current resources.
     *
     * Do not use directly, call getCurrentResources() instead.
     *
     * @var SelfCheckingResourceInterface[]|null
     */
    private $currentResources;

    /**
     * @param string $filePath
     * @param SelfCheckingResourceInterface[] $resources
     */
    public function __construct($filePath, array $resources)
    {
        $this->filePath = $filePath;
        $this->resources = $resources;

        $this->currentResources = null;
    }

    /**
     * @return SelfCheckingResourceInterface[]
     */
    public function getCurrentResources()
    {
        if (null === $this->currentResources) {
            $this->currentResources = is_file($this->filePath) ? unserialize(file_get_contents($this->filePath)) : array();
        }

        return $this->currentResources;
    }

    /**
     * @return bool
     */
    public function isFresh()
    {
        if ($this->didTheResourcesChanged()) {
            return false;
        }

        $time = filemtime($this->filePath);
        /** @var SelfCheckingResourceInterface $currentResource */
        foreach ($this->getCurrentResources() as $currentResource) {
            if (!$currentResource->isFresh($time)) {
                return false;
            }
        }

        return true;
    }

    public function commit()
    {
        $umask = umask();
        $filesystem = new Filesystem();

        $filesystem->dumpFile($this->filePath, serialize($this->resources));

        try {
            $filesystem->chmod($this->filePath, 0666, $umask);
        } catch (IOException $e) {
            // discard chmod failure (some filesystem may not support it)
        }

        if (function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
            @opcache_invalidate($this->filePath, true);
        }
    }

    /**
     * @return bool
     */
    private function didTheResourcesChanged()
    {
        $currentResources = $this->getCurrentResources();

        if (count($currentResources) !== count($this->resources)) {
            return true;
        }

        $currentResourcesUniqueRepresentation = $this->getResourcesUniqueRepresentation($currentResources);
        $resourcesUniqueRepresentation = $this->getResourcesUniqueRepresentation($this->resources);

        sort($currentResourcesUniqueRepresentation);
        sort($resourcesUniqueRepresentation);

        return $currentResourcesUniqueRepresentation !== $resourcesUniqueRepresentation;
    }

    /**
     * @param SelfCheckingResourceInterface[] $resources
     *
     * @return string[]
     */
    private function getResourcesUniqueRepresentation(array $resources)
    {
        return array_map(function (SelfCheckingResourceInterface $resource) {
            return sprintf('%s:%s', get_class($resource), $resource->__toString());
        }, $resources);
    }
}
