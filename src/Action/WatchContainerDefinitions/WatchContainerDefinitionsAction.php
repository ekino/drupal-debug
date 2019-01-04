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

namespace Ekino\Drupal\Debug\Action\WatchContainerDefinitions;

use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Helper\SettingsHelper;
use Ekino\Drupal\Debug\Kernel\Event\AfterSettingsInitializationEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;

class WatchContainerDefinitionsAction implements EventSubscriberActionInterface, ActionWithOptionsInterface
{
    /**
     * @var WatchContainerDefinitionsOptions
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'process',
        );
    }

    /**
     * @param WatchContainerDefinitionsOptions $options
     */
    public function __construct(WatchContainerDefinitionsOptions $options)
    {
        $this->options = $options;
    }

    public function process(AfterSettingsInitializationEvent $event): void
    {
        (new SettingsHelper())->override('[bootstrap_container_definition]', array(
            'services' => array(
                'cache.container' => array(
                    'class' => FileBackend::class,
                    'arguments' => array(
                        new FileCache($this->options->getCacheFilePath(), $this->options->getFilteredResourcesCollection($event->getEnabledModules(), $event->getEnabledThemes())),
                    ),
                ),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass(): string
    {
        return WatchContainerDefinitionsOptions::class;
    }
}
