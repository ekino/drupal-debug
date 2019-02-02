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

namespace Ekino\Drupal\Debug\Cache;

use Carbon\Carbon;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Ekino\Drupal\Debug\Resource\ResourcesFreshnessChecker;
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
     * @param string              $filePath
     * @param ResourcesCollection $resourcesCollection
     */
    public function __construct(string $filePath, ResourcesCollection $resourcesCollection)
    {
        $this->filePath = $filePath;

        $this->resourcesFreshnessChecker = new ResourcesFreshnessChecker(\sprintf('%s.meta', $filePath), $resourcesCollection);
    }

    /**
     * @return bool
     */
    public function isFresh(): bool
    {
        return $this->resourcesFreshnessChecker->isFresh();
    }

    /**
     * @return array|false
     */
    public function get()
    {
        if (!\is_file($this->filePath)) {
            return false;
        }

        try {
            $data = require $this->filePath;
        } catch (\Error $e) {
            return false;
        }

        if (!\is_array($data)) {
            throw new \LogicException('The file cache data content should be an array.');
        }

        if (!\array_key_exists('data', $data)) {
            throw new \LogicException('The file cache data content should have a "data" key.');
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = $this->get();
        if (!\is_array($data)) {
            return array();
        }

        return $data['data'];
    }

    /**
     * @param array $data
     */
    public function write(array $data): void
    {
        $currentData = $this->get();
        if (\is_array($currentData)) {
            $data = \array_merge($currentData['data'], $data);
        }

        $umask = \umask();
        $filesystem = new Filesystem();

        $filesystem->dumpFile($this->filePath, '<?php return '.\var_export(array(
            'date' => Carbon::now()->format(DATE_ATOM),
            'data' => $data,
        ), true).';');

        try {
            $filesystem->chmod($this->filePath, 0666, $umask);
        } catch (IOException $e) {
            // discard chmod failure (some filesystem may not support it)
        }

        if (\function_exists('opcache_invalidate') && \filter_var(\ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
            @\opcache_invalidate($this->filePath, true);
        }

        $this->resourcesFreshnessChecker->commit();
    }

    public function invalidate(): void
    {
        if (\is_file($this->filePath)) {
            \unlink($this->filePath);
        }
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getCurrentResourcesCollection(): ResourcesCollection
    {
        return $this->resourcesFreshnessChecker->getCurrentResourcesCollection();
    }
}
