<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Resource;

use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
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
     * @var ResourcesCollection
     */
    private $resourcesCollection;

    /**
     * Lazy loaded current resources collection.
     *
     * Do not use directly, call getCurrentResourcesCollection() method instead.
     *
     * @var ResourcesCollection|null
     */
    private $currentResourcesCollection;

    /**
     * @param string              $filePath
     * @param ResourcesCollection $resourcesCollection
     */
    public function __construct($filePath, ResourcesCollection $resourcesCollection)
    {
        $this->filePath = $filePath;
        $this->resourcesCollection = $resourcesCollection;

        $this->currentResourcesCollection = null;
    }

    /**
     * @return ResourcesCollection
     */
    public function getCurrentResourcesCollection()
    {
        if (!$this->currentResourcesCollection instanceof ResourcesCollection) {
            if (\is_file($this->filePath)) {
                $currentResourcesSerializedContent = @\file_get_contents($this->filePath);
                if (false === $currentResourcesSerializedContent) {
                    throw new \RuntimeException('The current resources serialized content could not be read.');
                }

                $this->currentResourcesCollection = \unserialize($currentResourcesSerializedContent);
                if (!$this->currentResourcesCollection instanceof ResourcesCollection) {
                    throw new \RuntimeException(\sprintf('The current resources unserialized content class should be "%s".', ResourcesCollection::class));
                }
            } else {
                $this->currentResourcesCollection = new ResourcesCollection();
            }
        }

        return $this->currentResourcesCollection;
    }

    /**
     * @return bool
     */
    public function isFresh()
    {
        if (!\is_file($this->filePath)) {
            return false;
        }

        if ($this->didTheResourcesChanged()) {
            return false;
        }

        $time = \filemtime($this->filePath);
        if (false === $time) {
            return false;
        }

        /** @var SelfCheckingResourceInterface $currentResource */
        foreach ($this->getCurrentResourcesCollection()->all() as $currentResource) {
            if (!$currentResource->isFresh($time)) {
                return false;
            }
        }

        return true;
    }

    public function commit()
    {
        $umask = \umask();
        $filesystem = new Filesystem();

        $filesystem->dumpFile($this->filePath, \serialize($this->resourcesCollection));

        try {
            $filesystem->chmod($this->filePath, 0666, $umask);
        } catch (IOException $e) {
            // discard chmod failure (some filesystem may not support it)
        }

        if (\function_exists('opcache_invalidate') && \filter_var(\ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
            @\opcache_invalidate($this->filePath, true);
        }
    }

    /**
     * @return bool
     */
    private function didTheResourcesChanged()
    {
        $currentResourcesCollection = $this->getCurrentResourcesCollection();

        if ($currentResourcesCollection->count() !== $this->resourcesCollection->count()) {
            return true;
        }

        $currentResourcesUniqueRepresentation = $this->getResourcesUniqueRepresentation($currentResourcesCollection);
        $resourcesUniqueRepresentation = $this->getResourcesUniqueRepresentation($this->resourcesCollection);

        \sort($currentResourcesUniqueRepresentation);
        \sort($resourcesUniqueRepresentation);

        return $currentResourcesUniqueRepresentation !== $resourcesUniqueRepresentation;
    }

    /**
     * @param ResourcesCollection $resourcesCollection
     *
     * @return string[]
     */
    private function getResourcesUniqueRepresentation(ResourcesCollection $resourcesCollection)
    {
        return \array_map(function (SelfCheckingResourceInterface $resource) {
            return \sprintf('%s:%s', \get_class($resource), $resource->__toString());
        }, $resourcesCollection->all());
    }
}
