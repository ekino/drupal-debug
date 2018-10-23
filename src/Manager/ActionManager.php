<?php

namespace Ekino\Drupal\Debug\Manager;

use Ekino\Drupal\Debug\Action\ActionInterface;
use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Helper\DefaultActionsHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionManager
{
    /**
     * @var string
     */
    private $appRoot;

    /**
     * @var EventSubscriberActionInterface[]
     */
    private $eventSubscriberActions;

    /**
     * @var CompilerPassActionInterface[]
     */
    private $compilerPassActions;

    /**
     * @param string $appRoot
     */
    public function __construct($appRoot)
    {
        $this->appRoot = $appRoot;

        $this->eventSubscriberActions = array();
        $this->compilerPassActions = array();
    }

    /**
     * @param ActionInterface[] $actions
     */
    public function process(array $actions)
    {
        if (empty($actions)) {
            $defaultActionsHelper = new DefaultActionsHelper($this->appRoot);

            $actions = $defaultActionsHelper->get();
        }

        foreach ($actions as $action) {
            if (!$action instanceof ActionInterface) {
                throw new \InvalidArgumentException(sprintf('Every action must implement the "%s" interface.', ActionInterface::class));
            }

            if ($action instanceof EventSubscriberActionInterface) {
                $this->eventSubscriberActions[] = $action;
            }

            if ($action instanceof CompilerPassActionInterface) {
                $this->compilerPassActions[] = $action;
            }
        }
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function addEventSubscriberActionsToEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        foreach ($this->eventSubscriberActions as $eventSubscriberAction) {
            $eventDispatcher->addSubscriber($eventSubscriberAction);
        }

        $this->eventSubscriberActions = array();
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function addCompilerPassActionsToContainerBuilder(ContainerBuilder $containerBuilder)
    {
        foreach ($this->compilerPassActions as $compilerPassAction) {
            $containerBuilder->addCompilerPass($compilerPassAction);
        }

        $this->compilerPassActions = array();
    }
}
