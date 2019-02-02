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

namespace Ekino\Drupal\Debug\Tests\Unit\Action;

use Ekino\Drupal\Debug\Action\ActionManager;
use Ekino\Drupal\Debug\Action\DisableCSSAggregation\DisableCSSAggregationAction;
use Ekino\Drupal\Debug\Action\DisableDynamicPageCache\DisableDynamicPageCacheAction;
use Ekino\Drupal\Debug\Action\DisableInternalPageCache\DisableInternalPageCacheAction;
use Ekino\Drupal\Debug\Action\DisableJSAggregation\DisableJSAggregationAction;
use Ekino\Drupal\Debug\Action\DisableRenderCache\DisableRenderCacheAction;
use Ekino\Drupal\Debug\Action\DisableTwigCache\DisableTwigCacheAction;
use Ekino\Drupal\Debug\Action\DisplayDumpLocation\DisplayDumpLocationAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPAction;
use Ekino\Drupal\Debug\Action\EnableDebugClassLoader\EnableDebugClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnableTwigDebug\EnableTwigDebugAction;
use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsAction;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsAction;
use Ekino\Drupal\Debug\Action\WatchModulesHooksImplementations\WatchModulesHooksImplementationsAction;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsAction;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Option\OptionsStack;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ActionManagerTest extends TestCase
{
    /**
     * @var string
     */
    private const CACHE_DIRECTORY_PATH = __DIR__.'/cache';

    /**
     * @var string
     */
    private const CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/drupal-debug.yml';

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var ActionManager
     */
    private $actionManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->clearCache();

        \putenv(\sprintf('%s=%s', ConfigurationManager::CONFIGURATION_CACHE_DIRECTORY_ENVIRONMENT_VARIABLE_NAME, self::CACHE_DIRECTORY_PATH));
        \putenv(\sprintf('%s=%s', ConfigurationManager::CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME, self::CONFIGURATION_FILE_PATH));

        ConfigurationManager::initialize();

        $optionsStack = $this->createMock(OptionsStack::class);
        $optionsStack
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn(null);

        $this->actionManager = new ActionManager('/foo', $optionsStack);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->clearCache();
    }

    public function testAddEventSubscriberActionsToEventDispatcher(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->exactly(13))
            ->method('addSubscriber')
            ->withConsecutive(
                array($this->isInstanceOf(DisableCSSAggregationAction::class)),
                array($this->isInstanceOf(DisableDynamicPageCacheAction::class)),
                array($this->isInstanceOf(DisableInternalPageCacheAction::class)),
                array($this->isInstanceOf(DisableJSAggregationAction::class)),
                array($this->isInstanceOf(DisableRenderCacheAction::class)),
                array($this->isInstanceOf(DisplayDumpLocationAction::class)),
                array($this->isInstanceOf(DisplayPrettyExceptionsAction::class)),
                array($this->isInstanceOf(DisplayPrettyExceptionsASAPAction::class)),
                array($this->isInstanceOf(EnableDebugClassLoaderAction::class)),
                array($this->isInstanceOf(ThrowErrorsAsExceptionsAction::class)),
                array($this->isInstanceOf(WatchContainerDefinitionsAction::class)),
                array($this->isInstanceOf(WatchModulesHooksImplementationsAction::class)),
                array($this->isInstanceOf(WatchRoutingDefinitionsAction::class))
            );

        $this->actionManager->addEventSubscriberActionsToEventDispatcher($eventDispatcher);
    }

    public function testAddCompilerPassActionsToContainerBuilder(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);
        $containerBuilder
            ->expects($this->exactly(4))
            ->method('addCompilerPass')
            ->withConsecutive(
                array($this->isInstanceOf(DisableTwigCacheAction::class)),
                array($this->isInstanceOf(DisplayPrettyExceptionsAction::class)),
                array($this->isInstanceOf(EnableTwigDebugAction::class)),
                array($this->isInstanceOf(WatchModulesHooksImplementationsAction::class))
            );

        $this->actionManager->addCompilerPassActionsToContainerBuilder($containerBuilder);
    }

    private function clearCache(): void
    {
        (new Filesystem())->remove(Finder::create()->in(self::CACHE_DIRECTORY_PATH));
    }
}
