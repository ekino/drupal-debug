<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Site\Settings;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Helper\CustomExtensionHelper;
use Ekino\Drupal\Debug\Helper\FileResourceHelper;
use Ekino\Drupal\Debug\Helper\SettingsHelper;

class WatchContainerDefinitionAction implements EventSubscriberActionInterface
{
    /**
     * @var string[]
     */
    const DEFAULT_FILE_RESOURCE_MASKS = array(
        '%machine_name%.services.yml',
        '%camel_case_machine_name%ServiceProvider.php'
    );

    /**
     * @var string
     */
    private $cacheFilePath;

    /**
     * @var ResourceInterface[]
     */
    private $resources;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'process',
        );
    }

    /**
     * @param string $cacheFilePath
     * @param ResourceInterface[] $resources
     */
    public function __construct($cacheFilePath, array $resources)
    {
        $this->cacheFilePath = $cacheFilePath;
        $this->resources = $resources;
    }

    public function process()
    {
        (new SettingsHelper())->override('[bootstrap_container_definition]', array(
            'services' => array(
                'cache.container' => array(
                    'class' => FileBackend::class,
                    'arguments' => array(
                        $this->cacheFilePath,
                        $this->resources,
                    ),
                ),
            ),
        ));
    }

    /**
     * @param string $appRoot
     *
     * @return WatchContainerDefinitionAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self(sprintf('%s/cache/container_definition.php', $appRoot), self::getDefaultResources((new CustomExtensionHelper($appRoot))->getCustomModules()));
    }

    /**
     * @param CustomModule[] $customModules
     *
     * @return ResourceInterface[]
     */
    public static function getDefaultResources(array $customModules)
    {
        $resources = array();

        $fileResourceHelper = new FileResourceHelper();

        /** @var CustomModule $customModule */
        foreach ($customModules as $customModule) {
            $resources = array_merge($resources, $fileResourceHelper->getFileResources($customModule->getRootPath(), self::DEFAULT_FILE_RESOURCE_MASKS, array(
                '%machine_name%' => $customModule->getMachineName(),
                '%camel_case_machine_name%' => $customModule->getCamelCaseMachineName(),
            )));
        }

        return $resources;
    }
}
