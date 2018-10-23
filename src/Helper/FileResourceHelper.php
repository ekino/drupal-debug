<?php

namespace Ekino\Drupal\Debug\Helper;

use Ekino\Drupal\Debug\Action\EnhanceClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnhanceContainerAction;
use Ekino\Drupal\Debug\Action\EnhanceDumpAction;
use Ekino\Drupal\Debug\Action\EnhanceExceptionPageAction;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\Config\Resource\FileResource;

class FileResourceHelper
{
    /**
     * @param string $rootPath
     * @param string[] $fileResourceMasks
     * @param string[] $replacePairs
     *
     * @return ResourceInterface[]
     */
    public function getFileResources($rootPath, array $fileResourceMasks, array $replacePairs)
    {
        $fileResources = array();

        foreach ($fileResourceMasks as $fileResourceMask) {
            $filePath = sprintf('%s/%s', $rootPath, strtr($fileResourceMask, $replacePairs));

            $fileResources[] = file_exists($filePath) ? new FileResource($filePath) : new FileExistenceResource($filePath);
        }

        return $fileResources;
    }
}
