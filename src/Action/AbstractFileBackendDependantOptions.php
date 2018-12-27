<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Exception\NotImplementedException;
use Ekino\Drupal\Debug\Extension\CustomExtensionDiscovery;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Ekino\Drupal\Debug\Resource\Model\CustomExtensionFileResource;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

abstract class AbstractFileBackendDependantOptions implements OptionsInterface
{
    /**
     * @var string
     */
    private $cacheFilePath;

    /**
     * @var ResourcesCollection
     */
    private $resourcesCollection;

    /**
     * @param string              $cacheFilePath
     * @param ResourcesCollection $resourcesCollection
     */
    public function __construct($cacheFilePath, ResourcesCollection $resourcesCollection)
    {
        $this->cacheFilePath = $cacheFilePath;
        $this->resourcesCollection = $resourcesCollection;
    }

    /**
     * @return string
     */
    public function getCacheFilePath()
    {
        return $this->cacheFilePath;
    }

    /**
     * @return ResourcesCollection
     */
    public function getResourcesCollection()
    {
        return $this->resourcesCollection;
    }

    /**
     * @param array $enabledModules
     * @param array $enabledThemes
     *
     * @return ResourcesCollection
     */
    public function getFilteredResourcesCollection(array $enabledModules, array $enabledThemes)
    {
        return new ResourcesCollection(\array_filter($this->resourcesCollection->all(), function (SelfCheckingResourceInterface $resource) use ($enabledModules, $enabledThemes) {
            if (!$resource instanceof CustomExtensionFileResource) {
                return true;
            }

            $customExtension = $resource->getCustomExtension();
            switch (\get_class($customExtension)) {
                case CustomModule::class:
                    return \in_array($customExtension->getMachineName(), $enabledModules);
                case CustomTheme::class:
                    return \in_array($customExtension->getMachineName(), $enabledThemes);
                default:
                    throw new NotImplementedException(\sprintf('The behavior for the "%s" custom extension class is not implemented.', \get_class($customExtension)));
            }
        }));
    }

    /**
     * @param string                $appRoot
     * @param DefaultsConfiguration $defaultsConfiguration
     *
     * @return AbstractFileBackendDependantOptions
     */
    public static function getDefault($appRoot, DefaultsConfiguration $defaultsConfiguration)
    {
        $defaultResources = array();

        $defaultModuleFileResourceMasks = static::getDefaultModuleFileResourceMasks();
        $defaultThemeFileResourceMasks = static::getDefaultThemeFileResourceMasks();
        if (!empty($defaultModuleFileResourceMasks) || !empty($defaultThemeFileResourceMasks)) {
            $customExtensionDiscovery = new CustomExtensionDiscovery($appRoot);
            $customModules = array();
            $customThemes = array();

            if (!empty($defaultModuleFileResourceMasks)) {
                $customModules = $customExtensionDiscovery->getCustomModules();
            }

            if (!empty($defaultThemeFileResourceMasks)) {
                $customThemes = $customExtensionDiscovery->getCustomThemes();
            }

            $defaultResources = static::getDefaultResources($customModules, $customThemes);
        }

        return new static(\sprintf('%s/%s', $defaultsConfiguration->getCacheDirectory(), static::getDefaultCacheFileName()), new ResourcesCollection($defaultResources));
    }

    /**
     * @return array
     */
    protected static function getDefaultModuleFileResourceMasks()
    {
        return array();
    }

    /**
     * @return array
     */
    protected static function getDefaultThemeFileResourceMasks()
    {
        return array();
    }

    /**
     * @return string
     */
    protected static function getDefaultCacheFileName()
    {
        return \rtrim((new \ReflectionClass(static::class))->getShortName(), 'Action');
    }

    /**
     * @param CustomModule[] $customModules
     * @param CustomTheme[]  $customThemes
     *
     * @return CustomExtensionFileResource[]
     */
    private static function getDefaultResources(array $customModules, array $customThemes)
    {
        $resources = array();

        if (!empty($customModules)) {
            /** @var CustomModule $customModule */
            foreach ($customModules as $customModule) {
                $replacePairs = array(
                    '%machine_name%' => $customModule->getMachineName(),
                    '%camel_case_machine_name%' => $customModule->getCamelCaseMachineName(),
                );

                foreach (static::getDefaultModuleFileResourceMasks() as $mask) {
                    $filePath = \sprintf('%s/%s', $customModule->getRootPath(), \strtr($mask, $replacePairs));

                    $resources[] = new CustomExtensionFileResource($filePath, $customModule);
                }
            }
        }

        if (!empty($customThemes)) {
            /** @var CustomTheme $customTheme */
            foreach ($customThemes as $customTheme) {
                $replacePairs = array(
                    '%machine_name%' => $customTheme->getMachineName(),
                );

                foreach (static::getDefaultThemeFileResourceMasks() as $mask) {
                    $filePath = \sprintf('%s/%s', $customTheme->getRootPath(), \strtr($mask, $replacePairs));

                    $resources[] = new CustomExtensionFileResource($filePath, $customTheme);
                }
            }
        }

        return $resources;
    }
}
