<?php

namespace Ekino\Drupal\Debug\Helper;

use Ekino\Drupal\Debug\Action\DisableCSSAggregationAction;
use Ekino\Drupal\Debug\Action\DisableDynamicPageCacheAction;
use Ekino\Drupal\Debug\Action\DisableInternalPageCacheAction;
use Ekino\Drupal\Debug\Action\DisableJSAggregationAction;
use Ekino\Drupal\Debug\Action\DisableRenderCacheAction;
use Ekino\Drupal\Debug\Action\DisableTwigCacheAction;
use Ekino\Drupal\Debug\Action\DisplayDumpLocationAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionASAPAction;
use Ekino\Drupal\Debug\Action\EnableDebugClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnableTwigDebugAction;
use Ekino\Drupal\Debug\Action\EnableTwigStrictVariablesAction;
use Ekino\Drupal\Debug\Action\ThrowErrorAsExceptionAction;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitionAction;
use Ekino\Drupal\Debug\Action\WatchHookAction;
use Ekino\Drupal\Debug\Action\WatchRoutingAction;

class DefaultActionsHelper
{
    /**
     * @var string
     */
    private $appRoot;

    /**
     * @param string $appRoot
     */
    public function __construct($appRoot)
    {
        $this->appRoot = $appRoot;
    }

    /**
     * @return ActionInterface[]
     */
    public function get()
    {
        return array(
            DisableCSSAggregationAction::getDefaultAction($this->appRoot),
            DisableDynamicPageCacheAction::getDefaultAction($this->appRoot),
            DisableInternalPageCacheAction::getDefaultAction($this->appRoot),
            DisableJSAggregationAction::getDefaultAction($this->appRoot),
            DisableRenderCacheAction::getDefaultAction($this->appRoot),
            DisableTwigCacheAction::getDefaultAction($this->appRoot),
            DisplayDumpLocationAction::getDefaultAction($this->appRoot),
            DisplayPrettyExceptionAction::getDefaultAction($this->appRoot),
            DisplayPrettyExceptionASAPAction::getDefaultAction($this->appRoot),
            EnableDebugClassLoaderAction::getDefaultAction($this->appRoot),
            EnableTwigDebugAction::getDefaultAction($this->appRoot),
            //EnableTwigStrictVariablesAction::getDefaultAction($this->appRoot),
            ThrowErrorAsExceptionAction::getDefaultAction($this->appRoot),
            WatchContainerDefinitionAction::getDefaultAction($this->appRoot),
            WatchHookAction::getDefaultAction($this->appRoot),
            WatchRoutingAction::getDefaultAction($this->appRoot),
        );
    }
}
