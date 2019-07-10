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

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes;

use Ekino\Drupal\Debug\Action\ActionRegistrar;
use Ekino\Drupal\Debug\ActionMetadata\ActionMetadataManager;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Kernel\DebugKernel;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestDebugKernelInstantiationEventDispatcher extends EventDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null): Event
    {
        TestDebugKernelInstantiation::$stack[] = \sprintf('dispatch.%s', $eventName);

        return new Event();
    }
}

class TestDebugKernelInstantiationActionRegistrar extends ActionRegistrar
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $appRoot, ActionMetadataManager $actionMetadataManager, ConfigurationManager $configurationManager, OptionsStack $optionsStack)
    {
        TestDebugKernelInstantiation::$stack[] = $appRoot;
        TestDebugKernelInstantiation::$stack[] = $actionMetadataManager;
        TestDebugKernelInstantiation::$stack[] = $configurationManager;
        TestDebugKernelInstantiation::$stack[] = $optionsStack;
    }

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriberActionsToEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        TestDebugKernelInstantiation::$stack[] = 'addEventSubscriberActionsToEventDispatcher';
    }
}

class TestDebugKernelInstantiation extends DebugKernel
{
    /**
     * @var array
     */
    public static $stack = array();

    /**
     * {@inheritdoc}
     */
    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return new TestDebugKernelInstantiationEventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionRegistrar(string $appRoot, ActionMetadataManager $actionMetadataManager, ConfigurationManager $configurationManager, OptionsStack $optionsStack): ActionRegistrar
    {
        return new TestDebugKernelInstantiationActionRegistrar($appRoot, $actionMetadataManager, $configurationManager, $optionsStack);
    }

    /**
     * {@inheritdoc}
     */
    public static function bootEnvironment($appRoot = null): void
    {
        self::$stack[] = 'bootEnvironment';
    }

    public static function reset(): void
    {
        self::$stack = array();
    }
}
