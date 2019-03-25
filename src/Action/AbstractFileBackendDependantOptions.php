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

use Ekino\Drupal\Debug\Configuration\CacheDirectoryPathConfigurationTrait;
use Ekino\Drupal\Debug\Configuration\Model\ActionConfiguration;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Exception\NotImplementedException;
use Ekino\Drupal\Debug\Extension\CustomExtensionDiscovery;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Ekino\Drupal\Debug\Resource\Model\CustomExtensionFileResource;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

abstract class AbstractFileBackendDependantOptions implements OptionsInterface
{
    use CacheDirectoryPathConfigurationTrait;

    /**
     * @var string
     */
    private $cacheFilePath;

    /**
     * @var ResourcesCollection
     */
    private $resourcesCollection;

    /**
     * @var bool|null
     */
    private static $canHaveBothExtensionTypeFileResourceMasks;

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
        return new ResourcesCollection(\array_filter($this->resourcesCollection->all(), static function (SelfCheckingResourceInterface $resource) use ($enabledModules, $enabledThemes): bool {
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
                    throw new NotImplementedException(\sprintf('The behavior for the "%s" custom extension file resource class is not implemented.', \get_class($customExtension)));
            }
        }));
    }

    /**
     * {@inheritdoc}
     */
    public static function addConfiguration(NodeBuilder $nodeBuilder, DefaultsConfiguration $defaultsConfiguration): void
    {
        $childrenNodeBuilders = array($nodeBuilder);

        if ($canHaveBothExtensionTypeFileResourceMasks = self::canHaveBothExtensionTypeFileResourceMasks()) {
            $childrenNodeBuilders = array();
            foreach (array('module', 'theme') as $extensionType) {
                $childrenNodeBuilders[] = $nodeBuilder
                    ->arrayNode($extensionType)
                        ->children();
            }
        }

        /** @var NodeBuilder $childrenNodeBuilder */
        foreach ($childrenNodeBuilders as $childrenNodeBuilder) {
            $childrenNodeBuilder
                ->booleanNode('include_defaults')
                    ->defaultTrue()
                ->end()
                ->arrayNode('custom_file_resource_masks')
                    ->scalarPrototype()
                ->end();

            if ($canHaveBothExtensionTypeFileResourceMasks) {
                $childrenNodeBuilder->end();
            }
        }

        self::addCacheDirectoryPathConfigurationNodeFromDefaultsConfiguration($nodeBuilder, $defaultsConfiguration);
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptions(string $appRoot, ActionConfiguration $actionConfiguration): OptionsInterface
    {
        $processedConfiguration = $actionConfiguration->getProcessedConfiguration();

        $resources = array();

        $customExtensionDiscovery = new CustomExtensionDiscovery($appRoot);

        if (static::canHaveModuleFileResourceMasks()) {
            $includeDefaults = ($canHaveBothExtensionTypeFileResourceMasks = self::canHaveBothExtensionTypeFileResourceMasks()) ? $processedConfiguration['module']['include_defaults'] : $processedConfiguration['include_defaults'];
            $customFileResourceMasks = $canHaveBothExtensionTypeFileResourceMasks ? $processedConfiguration['module']['custom_file_resource_masks'] : $processedConfiguration['custom_file_resource_masks'];

            $resources = self::getModuleResources($customExtensionDiscovery->getCustomModules(), $includeDefaults, $customFileResourceMasks);
        }

        if (static::canHaveThemeFileResourceMasks()) {
            $includeDefaults = ($canHaveBothExtensionTypeFileResourceMasks ?? ($canHaveBothExtensionTypeFileResourceMasks = self::canHaveBothExtensionTypeFileResourceMasks()) ? $processedConfiguration['theme']['include_defaults'] : $processedConfiguration['include_defaults']);
            $customFileResourceMasks = $canHaveBothExtensionTypeFileResourceMasks ? $processedConfiguration['theme']['custom_file_resource_masks'] : $processedConfiguration['custom_file_resource_masks'];

            $resources = \array_merge($resources, self::getThemeResources($customExtensionDiscovery->getCustomThemes(), $includeDefaults, $customFileResourceMasks));
        }

        return new static(
            \sprintf('%s/%s', self::getConfiguredCacheDirectoryPath($actionConfiguration), static::getCacheFileName()),
            new ResourcesCollection($resources)
        );
    }

    protected static function canHaveModuleFileResourceMasks(): bool
    {
        return false;
    }

    /**
     * @return string[]
     */
    protected static function getDefaultModuleFileResourceMasks(): array
    {
        return array();
    }

    protected static function canHaveThemeFileResourceMasks(): bool
    {
        return false;
    }

    /**
     * @return string[]
     */
    protected static function getDefaultThemeFileResourceMasks(): array
    {
        return array();
    }

    protected static function getCacheFileName(): string
    {
        return \rtrim((new \ReflectionClass(static::class))->getShortName(), 'Action');
    }

    /**
     * @param CustomModule[] $customModules
     * @param bool           $includeDefaults
     * @param string[]       $fileResourceMasks
     *
     * @return CustomExtensionFileResource[]
     */
    private static function getModuleResources(array $customModules, bool $includeDefaults, array $fileResourceMasks): array
    {
        if (empty($customModules) || (!$includeDefaults && empty($fileResourceMasks))) {
            return array();
        }

        $resources = array();

        if ($includeDefaults) {
            $fileResourceMasks = \array_merge($fileResourceMasks, static::getDefaultModuleFileResourceMasks());
        }

        /** @var CustomModule $customModule */
        foreach ($customModules as $customModule) {
            $replacePairs = array(
                '%machine_name%' => $customModule->getMachineName(),
                '%camel_case_machine_name%' => $customModule->getCamelCaseMachineName(),
            );

            foreach ($fileResourceMasks as $fileResourceMask) {
                $filePath = \sprintf('%s/%s', $customModule->getRootPath(), \strtr($fileResourceMask, $replacePairs));

                $resources[] = new CustomExtensionFileResource($filePath, $customModule);
            }
        }

        return $resources;
    }

    /**
     * @param CustomTheme[] $customThemes
     * @param bool          $includeDefaults
     * @param string[]      $fileResourceMasks
     *
     * @return CustomExtensionFileResource[]
     */
    private static function getThemeResources(array $customThemes, bool $includeDefaults, array $fileResourceMasks): array
    {
        if (empty($customThemes) || (!$includeDefaults && empty($fileResourceMasks))) {
            return array();
        }

        $resources = array();

        if ($includeDefaults) {
            $fileResourceMasks = \array_merge($fileResourceMasks, static::getDefaultThemeFileResourceMasks());
        }

        /** @var CustomTheme $customTheme */
        foreach ($customThemes as $customTheme) {
            $replacePairs = array(
                '%machine_name%' => $customTheme->getMachineName(),
            );

            foreach ($fileResourceMasks as $fileResourceMask) {
                $filePath = \sprintf('%s/%s', $customTheme->getRootPath(), \strtr($fileResourceMask, $replacePairs));

                $resources[] = new CustomExtensionFileResource($filePath, $customTheme);
            }
        }

        return $resources;
    }

    private static function canHaveBothExtensionTypeFileResourceMasks(): bool
    {
        if (!\is_bool(self::$canHaveBothExtensionTypeFileResourceMasks)) {
            self::$canHaveBothExtensionTypeFileResourceMasks = static::canHaveModuleFileResourceMasks() && static::canHaveThemeFileResourceMasks();
        }

        return self::$canHaveBothExtensionTypeFileResourceMasks;
    }
}
