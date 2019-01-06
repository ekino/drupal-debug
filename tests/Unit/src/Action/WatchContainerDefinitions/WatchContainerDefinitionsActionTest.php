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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\WatchContainerDefinitions;

use Drupal\Core\Site\Settings;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsAction;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsOptions;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Kernel\Event\AfterSettingsInitializationEvent;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class WatchContainerDefinitionsActionTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_settings_initialization' => 'process',
        ), WatchContainerDefinitionsAction::getSubscribedEvents());
    }

    /**
     * @runInSeparateProcess
     */
    public function testProcess(): void
    {
        new Settings(array());

        $filteredResourcesCollection = new ResourcesCollection(array(
            $this->createMock(SelfCheckingResourceInterface::class),
            $this->createMock(SelfCheckingResourceInterface::class),
        ));

        $watchContainerDefinitionsOptions = $this->createMock(WatchContainerDefinitionsOptions::class);
        $watchContainerDefinitionsOptions
            ->expects($this->atLeastOnce())
            ->method('getCacheFilePath')
            ->willReturn('/foo');
        $watchContainerDefinitionsOptions
            ->expects($this->atLeastOnce())
            ->method('getFilteredResourcesCollection')
            ->with(array('module_1'), array('theme_1'))
            ->willReturn($filteredResourcesCollection);

        $afterSettingsInitializationEvent = new AfterSettingsInitializationEvent(array('module_1'), array('theme_1'));

        (new WatchContainerDefinitionsAction($watchContainerDefinitionsOptions))->process($afterSettingsInitializationEvent);

        $this->assertEquals(array(
            'services' => array(
                'cache.container' => array(
                    'class' => FileBackend::class,
                    'arguments' => array(
                        new FileCache('/foo', $filteredResourcesCollection),
                    ),
                ),
            ),
        ), Settings::get('bootstrap_container_definition'));
    }

    public function testGetOptionsClass(): void
    {
        $this->assertSame('Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsOptions', WatchContainerDefinitionsAction::getOptionsClass());
    }
}
