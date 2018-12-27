<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes;

use Ekino\Drupal\Debug\Action\ActionManager;
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
    public function dispatch($eventName, Event $event = null)
    {
        TestDebugKernelInstantiation::$stack[] = \sprintf('dispatch.%s', $eventName);
    }
}

class TestDebugKernelInstantiationActionManager extends ActionManager
{
    /**
     * {@inheritdoc}
     */
    public function __construct($appRoot, OptionsStack $optionsStack)
    {
        TestDebugKernelInstantiation::$stack[] = $appRoot;
        TestDebugKernelInstantiation::$stack[] = $optionsStack;
    }

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriberActionsToEventDispatcher(EventDispatcherInterface $eventDispatcher)
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
    protected function getEventDispatcher()
    {
        return new TestDebugKernelInstantiationEventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionManager($appRoot, OptionsStack $optionsStack)
    {
        return new TestDebugKernelInstantiationActionManager($appRoot, $optionsStack);
    }

    /**
     * {@inheritdoc}
     */
    public static function bootEnvironment($appRoot = null)
    {
        self::$stack[] = 'bootEnvironment';
    }

    public static function reset()
    {
        self::$stack = array();
    }
}
