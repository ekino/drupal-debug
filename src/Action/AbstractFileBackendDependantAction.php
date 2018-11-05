<?php

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\Extension\CustomExtensionDiscovery;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Resource\CustomExtensionFileResource;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

abstract class AbstractFileBackendDependantAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $cacheFilePath;

    /**
     * @var SelfCheckingResourceInterface[]
     */
    protected $resources;

    /**
     * @param string $cacheFilePath
     * @param SelfCheckingResourceInterface[] $resources
     */
    public function __construct($cacheFilePath, array $resources)
    {
        $this->cacheFilePath = $cacheFilePath;
        $this->resources = $resources;
    }

    /**
     * @param string $appRoot
     *
     * @return AbstractFileBackendDependantAction
     *
     * @throws \InvalidArgumentException
     *
     * @throws \ReflectionException
     */
    public static function getDefaultAction($appRoot)
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

        return new static(sprintf('%s/cache/%s', $appRoot, static::getDefaultCacheFileName()), $defaultResources);
    }

    /**
     * @param CustomModule[] $customModules
     * @param CustomTheme[] $customThemes
     *
     * @return CustomExtensionFileResource[]
     */
    public static function getDefaultResources(array $customModules, array $customThemes)
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
                    $filePath = sprintf('%s/%s', $customModule->getRootPath(), strtr($mask, $replacePairs));

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
                    $filePath = sprintf('%s/%s', $customTheme->getRootPath(), strtr($mask, $replacePairs));

                    $resources[] = new CustomExtensionFileResource($filePath, $customTheme);
                }
            }
        }

        return $resources;
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
    abstract protected static function getDefaultCacheFileName();
}
