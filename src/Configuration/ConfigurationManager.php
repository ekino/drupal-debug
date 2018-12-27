<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Configuration;

use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Configuration\Model\SubstituteOriginalDrupalKernelConfiguration;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Parser;

class ConfigurationManager
{
    /**
     * @var string
     */
    const CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME = 'DRUPAL_DEBUG_CONFIGURATION_FILE_PATH';

    /**
     * @var string
     */
    const CONFIGURATION_CACHE_DIRECTORY_ENVIRONMENT_VARIABLE_NAME = 'DRUPAL_DEBUG_CONFIGURATION_CACHE_DIRECTORY';

    /**
     * @var string
     */
    const DEFAULT_CONFIGURATION_FILE_NAME = 'drupal-debug.yml.dist';

    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * @var array|null
     */
    private static $configurationFilePathInfo = null;

    /**
     * @var array|null
     */
    private static $processedConfigurations = null;

    /**
     * @var DefaultsConfiguration|null
     */
    private static $defaultsConfiguration = null;

    /**
     * @var SubstituteOriginalDrupalKernelConfiguration|null
     */
    private static $substituteOriginalDrupalKernelConfiguration = null;

    public static function initialize()
    {
        if (self::$initialized) {
            throw new \RuntimeException('The configuration should not be initialized twice.');
        }

        self::$initialized = true;

        $configurationCacheDirectory = \getenv(self::CONFIGURATION_CACHE_DIRECTORY_ENVIRONMENT_VARIABLE_NAME);
        if (false === $configurationCacheDirectory) {
            $configurationCacheDirectory = \sys_get_temp_dir();
        }

        $configurationFilePathInfo = self::getConfigurationFilePathInfo();
        list($configurationFilePath, $configurationFilePathExists) = $configurationFilePathInfo;

        $fileCache = new FileCache(\sprintf('%s/drupal_debug_configuration.php', $configurationCacheDirectory), new ResourcesCollection(array(
            $configurationFilePathExists ? new FileResource($configurationFilePath) : new FileExistenceResource($configurationFilePath),
            new FileResource(\sprintf('%s/Configuration.php', __DIR__)),
        )));
        if ($fileCache->isFresh() && !empty($data = $fileCache->getData())) {
            list(
                'defaults' => self::$defaultsConfiguration,
                'substitute_original_drupal_kernel' => self::$substituteOriginalDrupalKernelConfiguration
            ) = \array_map(function ($serializedConfiguration) {
                return \unserialize($serializedConfiguration);
            }, $data);
        } else {
            self::$configurationFilePathInfo = $configurationFilePathInfo;

            $fileCache->invalidate();
            $fileCache->write(array(
                'defaults' => \serialize(self::getDefaultsConfiguration()),
                'substitute_original_drupal_kernel' => \serialize(self::getSubstituteOriginalDrupalKernelConfiguration()),
            ));
        }
    }

    /**
     * @return DefaultsConfiguration
     */
    public static function getDefaultsConfiguration()
    {
        if (!self::$defaultsConfiguration instanceof DefaultsConfiguration) {
            self::process();

            self::$defaultsConfiguration = new DefaultsConfiguration(self::$processedConfigurations['defaults']);
        }

        return self::$defaultsConfiguration;
    }

    /**
     * @return SubstituteOriginalDrupalKernelConfiguration
     */
    public static function getSubstituteOriginalDrupalKernelConfiguration()
    {
        if (!self::$substituteOriginalDrupalKernelConfiguration instanceof SubstituteOriginalDrupalKernelConfiguration) {
            self::process();

            self::$substituteOriginalDrupalKernelConfiguration = new SubstituteOriginalDrupalKernelConfiguration(self::$processedConfigurations['substitute_original_drupal_kernel']);
        }

        return self::$substituteOriginalDrupalKernelConfiguration;
    }

    /**
     * @return array
     */
    public static function getConfigurationFilePathInfo()
    {
        $possibleConfigurationFilePath = \getenv(self::CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME);
        if (false === $possibleConfigurationFilePath) {
            // The default configuration file location is the same than the vendor directory.
            $possibleAutoloadPaths = array(
                // Vendor of a project : Configuration\src\drupal-debug\ekino\autoload.php
                \sprintf('%s/../../../../autoload.php', __DIR__),
                // Directly this project : Configuration\src\/vendor/autoload.php
                \sprintf('%s/../../vendor/autoload.php', __DIR__),
                // For other cases (if they exist), please use the dedicated environment variable.
            );

            foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
                if (\is_file($possibleAutoloadPath)) {
                    $possibleConfigurationFilePath = \sprintf('%s/../%s', \dirname($possibleAutoloadPath), self::DEFAULT_CONFIGURATION_FILE_NAME);

                    break;
                }
            }

            if (false === $possibleConfigurationFilePath) {
                throw new \RuntimeException('The composer autoload.php file could not be found.');
            }
        }

        $possibleConfigurationFilePaths = array(
            $possibleConfigurationFilePath,
            \rtrim($possibleConfigurationFilePath, '.dist'),
        );

        $exists = false;
        foreach ($possibleConfigurationFilePaths as $possibleConfigurationFilePath) {
            if (\is_file($possibleConfigurationFilePath)) {
                $exists = true;

                break;
            }
        }

        return array(
            $possibleConfigurationFilePath,
            $exists,
        );
    }

    private static function process()
    {
        if (!self::$initialized) {
            throw new \RuntimeException('The configuration has not been initialized.');
        }

        if (\is_array(self::$processedConfigurations)) {
            return;
        }

        $processedConfigurations = (new Processor())->process((new Configuration())->getConfigTreeBuilder()->buildTree(), self::getConfigurationFileContent());
        self::$processedConfigurations = self::makeRelativePathsAbsolutes($processedConfigurations);
    }

    /**
     * @return array
     */
    private static function getConfigurationFileContent()
    {
        list($configurationFilePath, $configurationFilePathExists) = self::$configurationFilePathInfo;
        if (!$configurationFilePathExists) {
            return array();
        }

        $parser = new Parser();
        $content = $parser->parseFile($configurationFilePath);
        if (!\is_array($content)) {
            throw new \RuntimeException('The content of the drupal-debug configuration file should be an array.');
        }

        return $content;
    }

    /**
     * @param array $processedConfigurations
     *
     * @return array
     */
    private static function makeRelativePathsAbsolutes(array $processedConfigurations)
    {
        $filesystem = new Filesystem();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $configurationPaths = array(
            '[defaults][cache_directory]',
            '[defaults][logger][file_path]',
            '[substitute_original_drupal_kernel][composer_autoload_file_path]',
            '[substitute_original_drupal_kernel][cache_directory]',
        );

        list($configurationFilePath) = self::$configurationFilePathInfo;
        $configurationFilePathDirectory = \dirname($configurationFilePath);
        foreach ($configurationPaths as $configurationPath) {
            if (!$propertyAccessor->isReadable($processedConfigurations, $configurationPath)) {
                continue;
            }

            $path = $propertyAccessor->getValue($processedConfigurations, $configurationPath);
            if (null === $path || '' === $path) {
                continue;
            }

            if (!$filesystem->isAbsolutePath($path)) {
                $propertyAccessor->setValue($processedConfigurations, $configurationPath, \sprintf('%s/%s', $configurationFilePathDirectory, $path));
            }
        }

        return $processedConfigurations;
    }
}
