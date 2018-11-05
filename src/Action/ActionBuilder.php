<?php

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
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class ActionBuilder
{
    /**
     * @var ActionInterface[]
     */
    private $actions;

    private function __construct()
    {
        $this->actions = array();
    }

    /**
     * @return ActionBuilder
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return ActionInterface[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     *
     * @throws \InvalidArgumentException
     *
     * @throws \ReflectionException
     *
     * @throws \Exception
     */
    public function withAllDefaults($appRoot)
    {
        return $this->withDefaultDisableCSSAggregation($appRoot)
            ->withDefaultDisableDynamicPageCache($appRoot)
            ->withDefaultDisableInternalPageCache($appRoot)
            ->withDefaultDisableJSAggregation($appRoot)
            ->withDefaultDisableRenderCache($appRoot)
            ->withDefaultDisableTwigCache($appRoot)
            ->withDefaultDisplayDumpLocation($appRoot)
            ->withDefaultDisplayPrettyExceptions($appRoot)
            ->withDefaultDisplayPrettyExceptionsASAP($appRoot)
            ->withDefaultEnableDebugClassLoader($appRoot)
            ->withDefaultEnableTwigDebug($appRoot)
            ->withDefaultEnableTwigStrictVariables($appRoot)
            ->withDefaultThrowErrorsAsExceptions($appRoot)
            ->withDefaultWatchContainerDefinitions($appRoot)
            ->withDefaultWatchHooksImplementations($appRoot)
            ->withDefaultWatchRoutingDefinitions($appRoot);
    }

    /**
     * @return ActionBuilder
     */
    public function withDisableCSSAggregation()
    {
        $this->addAction(new DisableCSSAggregationAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisableCSSAggregation($appRoot)
    {
        $this->addAction(DisableCSSAggregationAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisableCSSAggregation()
    {
        $this->removeAction(DisableCSSAggregationAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withDisableDynamicPageCache()
    {
        $this->addAction(new DisableDynamicPageCacheAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisableDynamicPageCache($appRoot)
    {
        $this->addAction(DisableDynamicPageCacheAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisableDynamicPageCache()
    {
        $this->removeAction(DisableDynamicPageCacheAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withDisableInternalPageCache()
    {
        $this->addAction(new DisableInternalPageCacheAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisableInternalPageCache($appRoot)
    {
        $this->addAction(DisableInternalPageCacheAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisableInternalPageCache()
    {
        $this->removeAction(DisableInternalPageCacheAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withDisableJSAggregation()
    {
        $this->addAction(new DisableJSAggregationAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisableJSAggregation($appRoot)
    {
        $this->addAction(DisableJSAggregationAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisableJSAggregation()
    {
        $this->removeAction(DisableJSAggregationAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withDisableRenderCache()
    {
        $this->addAction(new DisableRenderCacheAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisableRenderCache($appRoot)
    {
        $this->addAction(DisableRenderCacheAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisableRenderCache()
    {
        $this->removeAction(DisableRenderCacheAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withDisableTwigCache()
    {
        $this->addAction(new DisableTwigCacheAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisableTwigCache($appRoot)
    {
        $this->addAction(DisableTwigCacheAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisableTwigCache()
    {
        $this->removeAction(DisableTwigCacheAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withDisplayDumpLocation()
    {
        $this->addAction(new DisplayDumpLocationAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisplayDumpLocation($appRoot)
    {
        $this->addAction(DisplayDumpLocationAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisplayDumpLocation()
    {
        $this->removeAction(DisplayDumpLocationAction::class);

        return $this;
    }

    /**
     * @param string $charset
     * @param string $fileLinkFormat
     * @param LoggerInterface|null $logger
     *
     * @return ActionBuilder
     */
    public function withDisplayPrettyExceptions($charset, $fileLinkFormat, LoggerInterface $logger = null)
    {
        $this->addAction(new DisplayPrettyExceptionsAction($charset, $fileLinkFormat, $logger));

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     *
     * @throws \Exception
     */
    public function withDefaultDisplayPrettyExceptions($appRoot)
    {
        $this->addAction(DisplayPrettyExceptionsAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisplayPrettyExceptions()
    {
        $this->removeAction(DisplayPrettyExceptionsAction::class);

        return $this;
    }

    /**
     * @param string $charset
     * @param string $fileLinkFormat
     *
     * @return ActionBuilder
     */
    public function withDisplayPrettyExceptionsASAP($charset, $fileLinkFormat)
    {
        $this->addAction(new DisplayPrettyExceptionsASAPAction($charset, $fileLinkFormat));

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultDisplayPrettyExceptionsASAP($appRoot)
    {
        $this->addAction(DisplayPrettyExceptionsASAPAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutDisplayPrettyExceptionsASAP()
    {
        $this->removeAction(DisplayPrettyExceptionsASAPAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withEnableDebugClassLoader()
    {
        $this->addAction(new EnableDebugClassLoaderAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultEnableDebugClassLoader($appRoot)
    {
        $this->addAction(EnableDebugClassLoaderAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutEnableDebugClassLoader()
    {
        $this->removeAction(EnableDebugClassLoaderAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withEnableTwigDebug()
    {
        $this->addAction(new EnableTwigDebugAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultEnableTwigDebug($appRoot)
    {
        $this->addAction(EnableTwigDebugAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutEnableTwigDebug()
    {
        $this->removeAction(EnableTwigDebugAction::class);

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withEnableTwigStrictVariables()
    {
        $this->addAction(new EnableTwigStrictVariablesAction());

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     */
    public function withDefaultEnableTwigStrictVariables($appRoot)
    {
        $this->addAction(EnableTwigStrictVariablesAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutEnableTwigStrictVariables()
    {
        $this->removeAction(EnableTwigStrictVariablesAction::class);

        return $this;
    }

    /**
     * @param int $levels
     * @param LoggerInterface|null $logger
     *
     * @return ActionBuilder
     */
    public function withThrowErrorsAsExceptions($levels, LoggerInterface $logger = null)
    {
        $this->addAction(new ThrowErrorsAsExceptionsAction($levels, $logger));

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     *
     * @throws \Exception
     */
    public function withDefaultThrowErrorsAsExceptions($appRoot)
    {
        $this->addAction(ThrowErrorsAsExceptionsAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutThrowErrorsAsExceptions()
    {
        $this->removeAction(ThrowErrorsAsExceptionsAction::class);

        return $this;
    }

    /**
     * @param string $cacheFilePath
     * @param SelfCheckingResourceInterface[] $resources
     *
     * @return ActionBuilder
     */
    public function withWatchContainerDefinitions($cacheFilePath, array $resources)
    {
        $this->addAction(new WatchContainerDefinitionsAction($cacheFilePath, $resources));

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     *
     * @throws \InvalidArgumentException
     *
     * @throws \ReflectionException
     */
    public function withDefaultWatchContainerDefinitions($appRoot)
    {
        $this->addAction(WatchContainerDefinitionsAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutWatchContainerDefinitions()
    {
        $this->removeAction(WatchContainerDefinitionsAction::class);

        return $this;
    }

    /**
     * @param string $cacheFilePath
     * @param SelfCheckingResourceInterface[] $resources
     *
     * @return ActionBuilder
     */
    public function withWatchHooksImplementations($cacheFilePath, array $resources)
    {
        $this->addAction(new WatchHooksImplementationsAction($cacheFilePath, $resources));

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     *
     * @throws \InvalidArgumentException
     *
     * @throws \ReflectionException
     */
    public function withDefaultWatchHooksImplementations($appRoot)
    {
        $this->addAction(WatchHooksImplementationsAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutWatchHooksImplementations()
    {
        $this->removeAction(WatchHooksImplementationsAction::class);

        return $this;
    }

    /**
     * @param string $cacheFilePath
     * @param SelfCheckingResourceInterface[] $resources
     *
     * @return ActionBuilder
     */
    public function withWatchRoutingDefinitions($cacheFilePath, array $resources)
    {
        $this->addAction(new WatchRoutingDefinitionsAction($cacheFilePath, $resources));

        return $this;
    }

    /**
     * @param string $appRoot
     *
     * @return ActionBuilder
     *
     * @throws \InvalidArgumentException
     *
     * @throws \ReflectionException
     */
    public function withDefaultWatchRoutingDefinitions($appRoot)
    {
        $this->addAction(WatchRoutingDefinitionsAction::getDefaultAction($appRoot));

        return $this;
    }

    /**
     * @return ActionBuilder
     */
    public function withoutWatchRoutingDefinitions()
    {
        $this->removeAction(WatchRoutingDefinitionsAction::class);

        return $this;
    }

    /**
     * @param ActionInterface $action
     */
    private function addAction(ActionInterface $action)
    {
        $this->actions[get_class($action)] = $action;
    }

    /**
     * @param string $actionClass
     */
    private function removeAction($actionClass)
    {
        unset($this->actions[$actionClass]);
    }
}
