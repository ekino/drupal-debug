<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Configuration\Model;

use Composer\Autoload\ClassLoader;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;

class SubstituteOriginalDrupalKernelConfiguration extends AbstractConfiguration
{
    /**
     * @var ClassLoader|null
     */
    private $classLoader;

    /**
     * @param array $processedConfiguration
     */
    public function __construct(array $processedConfiguration)
    {
        parent::__construct($processedConfiguration);

        $this->classLoader = null;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->processedConfiguration['enabled'];
    }

    /**
     * @return ClassLoader
     */
    public function getClassLoader()
    {
        if (!$this->classLoader instanceof ClassLoader) {
            if (!$this->isEnabled()) {
                throw new \LogicException('The class loader getter should not be called if the original DrupalKernel substitution is disabled.');
            }

            $classLoader = require $this->processedConfiguration['composer_autoload_file_path'];
            if (!$classLoader instanceof ClassLoader) {
                throw new \RuntimeException(\sprintf('The composer autoload.php file did not return a "%s" instance.', ClassLoader::class));
            }

            $this->classLoader = $classLoader;
        }

        return $this->classLoader;
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        if (!$this->isEnabled()) {
            throw new \LogicException('The cache directory getter should not be called if the original DrupalKernel substitution is disabled.');
        }

        if (!isset($this->processedConfiguration['cache_directory'])) {
            return ConfigurationManager::getDefaultsConfiguration()->getCacheDirectory();
        }

        return $this->processedConfiguration['cache_directory'];
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return \serialize(array(
            $this->processedConfiguration,
            null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->processedConfiguration, $this->classLoader) = \unserialize($serialized);
    }
}
