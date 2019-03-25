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

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\ActionMetadata\ActionMetadataManager;
use Ekino\Drupal\Debug\ActionMetadata\Model\ActionMetadata;
use Ekino\Drupal\Debug\ActionMetadata\Model\ActionWithOptionsMetadata;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionRegistrar
{
    /**
     * @var EventSubscriberActionInterface[]
     */
    private $eventSubscriberActions;

    /**
     * @var CompilerPassActionInterface[]
     */
    private $compilerPassActions;

    public function __construct(
        string $appRoot,
        ActionMetadataManager $actionMetadataManager,
        ConfigurationManager $configurationManager,
        OptionsStack $optionsStack
    ) {
        $this->eventSubscriberActions = array();
        $this->compilerPassActions = array();

        foreach ($this->getActions($appRoot, $actionMetadataManager, $configurationManager, $optionsStack) as $action) {
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
    public function addEventSubscriberActionsToEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        foreach ($this->eventSubscriberActions as $eventSubscriberAction) {
            $eventDispatcher->addSubscriber($eventSubscriberAction);
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function addCompilerPassActionsToContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        foreach ($this->compilerPassActions as $compilerPassAction) {
            $containerBuilder->addCompilerPass($compilerPassAction);
        }
    }

    /**
     * @param string                $appRoot
     * @param OptionsStack          $optionsStack
     * @param ActionMetadataManager $actionMetadataManager
     * @param ConfigurationManager  $configurationManager
     *
     * @return ActionInterface[]
     */
    private function getActions(
        string $appRoot,
        ActionMetadataManager $actionMetadataManager,
        ConfigurationManager $configurationManager,
        OptionsStack $optionsStack
    ): array {
        $actions = array();

        /** @var ActionMetadata $actionMetadata */
        foreach ($actionMetadataManager->all() as $shortName => $actionMetadata) {
            $actionConfiguration = $configurationManager->getActionConfiguration($shortName);

            if (!$actionConfiguration->isEnabled()) {
                continue;
            }

            $args = array();
            if ($actionMetadata instanceof ActionWithOptionsMetadata) {
                $optionsClass = $actionMetadata->getOptionsClass();

                $options = $optionsStack->get($optionsClass);
                if (!$options instanceof OptionsInterface) {
                    $options = $optionsClass::getOptions($appRoot, $actionConfiguration);
                }

                $args[] = $options;
            }

            /** @var ActionInterface $action */
            $action = $actionMetadata->getReflectionClass()->newInstanceArgs($args);
            $actions[] = $action;
        }

        return $actions;
    }
}
