<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes;

use Ekino\Drupal\Debug\Action\ActionManager;
use Ekino\Drupal\Debug\Kernel\DebugKernel;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestConfigStorage
{
    /**
     * @param string $name
     *
     * @return array
     */
    public function read($name)
    {
        if ('core.extension' !== $name) {
            throw new \InvalidArgumentException('The only expected config read is the "core.extension" one.');
        }

        return array(
            'module' => array(
                'fcy' => true,
            ),
            'theme' => array(
                'ccc' => true,
            ),
        );
    }
}

class TestDebugKernelActionManager extends ActionManager
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriberActionsToEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
    }
}

class TestDebugKernel extends DebugKernel
{
    /**
     * {@inheritdoc}
     */
    protected function getConfigStorage()
    {
        return new TestConfigStorage();
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionManager($appRoot, OptionsStack $optionsStack)
    {
        return new TestDebugKernelActionManager();
    }
}
