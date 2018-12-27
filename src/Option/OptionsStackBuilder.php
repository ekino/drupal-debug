<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Option;

use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsOptions;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPOptions;
use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsOptions;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsOptions;
use Ekino\Drupal\Debug\Action\WatchHooksImplementations\WatchHooksImplementationsOptions;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsOptions;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Psr\Log\LoggerInterface;

class OptionsStackBuilder
{
    /**
     * @var OptionsInterface[]
     */
    private $options;

    private function __construct()
    {
        $this->options = array();
    }

    /**
     * @return OptionsStackBuilder
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return OptionsStack
     */
    public function getOptionsStack()
    {
        return OptionsStack::create($this->options);
    }

    /**
     * @param string|null          $charset
     * @param string|null          $fileLinkFormat
     * @param LoggerInterface|null $logger
     */
    public function setDisplayPrettyExceptionsOptions($charset, $fileLinkFormat, LoggerInterface $logger = null)
    {
        $this->set(new DisplayPrettyExceptionsOptions($charset, $fileLinkFormat, $logger));

        return $this;
    }

    /**
     * @param string|null $charset
     * @param string|null $fileLinkFormat
     */
    public function setDisplayPrettyExceptionsASAPOptions($charset, $fileLinkFormat)
    {
        $this->set(new DisplayPrettyExceptionsASAPOptions($charset, $fileLinkFormat));
    }

    /**
     * @param int                  $levels
     * @param LoggerInterface|null $logger
     */
    public function setThrowErrorsAsExceptionsOptions($levels, LoggerInterface $logger = null)
    {
        $this->set(new ThrowErrorsAsExceptionsOptions($levels, $logger));

        return $this;
    }

    /**
     * @param string              $cacheFilePath
     * @param ResourcesCollection $resourcesCollection
     */
    public function setWatchContainerDefinitionsOptions($cacheFilePath, ResourcesCollection $resourcesCollection)
    {
        $this->set(new WatchContainerDefinitionsOptions($cacheFilePath, $resourcesCollection));
    }

    /**
     * @param string              $cacheFilePath
     * @param ResourcesCollection $resourcesCollection
     */
    public function setWatchHooksImplementationsOptions($cacheFilePath, ResourcesCollection $resourcesCollection)
    {
        $this->set(new WatchHooksImplementationsOptions($cacheFilePath, $resourcesCollection));
    }

    /**
     * @param string              $cacheFilePath
     * @param ResourcesCollection $resourcesCollection
     */
    public function setWatchRoutingDefinitionsOptions($cacheFilePath, ResourcesCollection $resourcesCollection)
    {
        $this->set(new WatchRoutingDefinitionsOptions($cacheFilePath, $resourcesCollection));
    }

    /**
     * @param OptionsInterface $options
     */
    private function set(OptionsInterface $options)
    {
        $this->options[\get_class($options)] = $options;
    }
}
