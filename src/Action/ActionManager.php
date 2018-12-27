<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

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
use Ekino\Drupal\Debug\Action\EnableTwigStrictVariables\EnableTwigStrictVariablesAction;
use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsAction;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsAction;
use Ekino\Drupal\Debug\Action\WatchHooksImplementations\WatchHooksImplementationsAction;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsAction;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionManager
{
    /**
     * @var EventSubscriberActionInterface[]
     */
    private $eventSubscriberActions;

    /**
     * @var CompilerPassActionInterface[]
     */
    private $compilerPassActions;

    /**
     * @param string       $appRoot
     * @param OptionsStack $optionsStack
     */
    public function __construct($appRoot, OptionsStack $optionsStack)
    {
        $this->eventSubscriberActions = array();
        $this->compilerPassActions = array();

        foreach ($this->getActions($appRoot, $optionsStack) as $action) {
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
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function addCompilerPassActionsToContainerBuilder(ContainerBuilder $containerBuilder)
    {
        foreach ($this->compilerPassActions as $compilerPassAction) {
            $containerBuilder->addCompilerPass($compilerPassAction);
        }
    }

    /**
     * @param string       $appRoot
     * @param OptionsStack $optionsStack
     *
     * @return ActionInterface[]
     */
    private function getActions($appRoot, OptionsStack $optionsStack)
    {
        $actionsClasses = array(
            DisableCSSAggregationAction::class,
            DisableDynamicPageCacheAction::class,
            DisableInternalPageCacheAction::class,
            DisableJSAggregationAction::class,
            DisableRenderCacheAction::class,
            DisableTwigCacheAction::class,
            DisplayDumpLocationAction::class,
            DisplayPrettyExceptionsAction::class,
            DisplayPrettyExceptionsASAPAction::class,
            EnableDebugClassLoaderAction::class,
            EnableTwigDebugAction::class,
            // Drupal Core does not handle strict variables. temporarily
            // @see https://www.drupal.org/project/drupal/issues/2445705
            //EnableTwigStrictVariablesAction::class,
            ThrowErrorsAsExceptionsAction::class,
            WatchContainerDefinitionsAction::class,
            WatchHooksImplementationsAction::class,
            WatchRoutingDefinitionsAction::class,
        );

        $defaultsConfiguration = ConfigurationManager::getDefaultsConfiguration();

        $actions = array();
        foreach ($actionsClasses as $actionClass) {
            $refl = new \ReflectionClass($actionClass);

            $args = array();
            if ($refl->implementsInterface(ActionWithOptionsInterface::class)) {
                $optionsClass = $refl->getMethod('getOptionsClass')->invoke(null);

                $options = $optionsStack->get($optionsClass);
                if (!$options instanceof OptionsInterface) {
                    $options = (new \ReflectionClass($optionsClass))->getMethod('getDefault')->invokeArgs(null, array(
                        $appRoot,
                        $defaultsConfiguration,
                    ));
                }

                $args[] = $options;
            }

            /** @var ActionInterface $action */
            $action = $refl->newInstanceArgs($args);

            $actions[] = $action;
        }

        return $actions;
    }
}
