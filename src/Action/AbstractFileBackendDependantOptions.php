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
    public function __construct(string $cacheFilePath, ResourcesCollection $resourcesCollection)
    {
        $this->cacheFilePath = $cacheFilePath;
        $this->resourcesCollection = $resourcesCollection;
    }

    /**
     * @return string
     */
    public function getCacheFilePath(): string
    {
        return $this->cacheFilePath;
    }

    /**
     * @return ResourcesCollection
     */
    public function getResourcesCollection(): ResourcesCollection
    {
        return $this->resourcesCollection;
    }

    /**
     * @param string[] $enabledModules
     * @param string[] $enabledThemes
     *
     * @return ResourcesCollection
     */
    public function getFilteredResourcesCollection(array $enabledModules, array $enabledThemes): ResourcesCollection
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
    public static function getDefault(string $appRoot, DefaultsConfiguration $defaultsConfiguration): OptionsInterface
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
     * @return string[]
     */
    protected static function getDefaultModuleFileResourceMasks(): array
    {
        return array();
    }

    /**
     * @return string[]
     */
    protected static function getDefaultThemeFileResourceMasks(): array
    {
        return array();
    }

    /**
     * @return string
     */
    protected static function getDefaultCacheFileName(): string
    {
        return \rtrim((new \ReflectionClass(static::class))->getShortName(), 'Action');
    }

    /**
     * @param CustomModule[] $customModules
     * @param CustomTheme[]  $customThemes
     *
     * @return CustomExtensionFileResource[]
     */
    private static function getDefaultResources(array $customModules, array $customThemes): array
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
