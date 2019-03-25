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

namespace Ekino\Drupal\Debug\ActionMetadata;

use Ekino\Drupal\Debug\Action\DisableCSSAggregation\DisableCSSAggregationAction;
use Ekino\Drupal\Debug\Action\DisableDynamicPageCache\DisableDynamicPageCacheAction;
use Ekino\Drupal\Debug\Action\DisableInternalPageCache\DisableInternalPageCacheAction;
use Ekino\Drupal\Debug\Action\DisableJSAggregation\DisableJSAggregationAction;
use Ekino\Drupal\Debug\Action\DisableRenderCache\DisableRenderCacheAction;
use Ekino\Drupal\Debug\Action\DisableTwigCache\DisableTwigCacheAction;
use Ekino\Drupal\Debug\Action\DisplayDumpLocation\DisplayDumpLocationAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsOptions;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPOptions;
use Ekino\Drupal\Debug\Action\EnableDebugClassLoader\EnableDebugClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnableTwigDebug\EnableTwigDebugAction;
use Ekino\Drupal\Debug\Action\EnableTwigStrictVariables\EnableTwigStrictVariablesAction;
use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsAction;
use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsOptions;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsAction;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsOptions;
use Ekino\Drupal\Debug\Action\WatchModulesHooksImplementations\WatchModulesHooksImplementationsAction;
use Ekino\Drupal\Debug\Action\WatchModulesHooksImplementations\WatchModulesHooksImplementationsOptions;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsAction;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsOptions;
use Ekino\Drupal\Debug\ActionMetadata\Model\ActionMetadata;
use Ekino\Drupal\Debug\ActionMetadata\Model\ActionWithOptionsMetadata;

class ActionMetadataManager
{
    private const CORE_ACTIONS = array(
        'disable_css_aggregation' => array(
            ActionMetadata::class,
            DisableCSSAggregationAction::class,
            array(),
        ),
        'disable_dynamic_page_cache' => array(
            ActionMetadata::class,
            DisableDynamicPageCacheAction::class,
            array(),
        ),
        'disable_internal_page_cache' => array(
            ActionMetadata::class,
            DisableInternalPageCacheAction::class,
            array(),
        ),
        'disable_js_aggregation' => array(
            ActionMetadata::class,
            DisableJSAggregationAction::class,
            array(),
        ),
        'disable_render_cache' => array(
            ActionMetadata::class,
            DisableRenderCacheAction::class,
            array(),
        ),
        'disable_twig_cache' => array(
            ActionMetadata::class,
            DisableTwigCacheAction::class,
            array(),
        ),
        'display_dump_location' => array(
            ActionMetadata::class,
            DisplayDumpLocationAction::class,
            array(),
        ),
        'display_pretty_exceptions' => array(
            ActionWithOptionsMetadata::class,
            DisplayPrettyExceptionsAction::class,
            array(
                DisplayPrettyExceptionsOptions::class,
            ),
        ),
        'display_pretty_exceptions_asap' => array(
            ActionWithOptionsMetadata::class,
            DisplayPrettyExceptionsASAPAction::class,
            array(
                DisplayPrettyExceptionsASAPOptions::class,
            ),
        ),
        'enable_debug_class_loader' => array(
            ActionMetadata::class,
            EnableDebugClassLoaderAction::class,
            array(),
        ),
        'enable_twig_debug' => array(
            ActionMetadata::class,
            EnableTwigDebugAction::class,
            array(),
        ),
        'enable_twig_strict_variables' => array(
            ActionMetadata::class,
            EnableTwigStrictVariablesAction::class,
            array(),
        ),
        'throw_errors_as_exceptions' => array(
            ActionWithOptionsMetadata::class,
            ThrowErrorsAsExceptionsAction::class,
            array(
                ThrowErrorsAsExceptionsOptions::class,
            ),
        ),
        'watch_container_definitions' => array(
            ActionWithOptionsMetadata::class,
            WatchContainerDefinitionsAction::class,
            array(
                WatchContainerDefinitionsOptions::class,
            ),
        ),
        'watch_modules_hooks_implementations' => array(
            ActionWithOptionsMetadata::class,
            WatchModulesHooksImplementationsAction::class,
            array(
                WatchModulesHooksImplementationsOptions::class,
            ),
        ),
        'watch_routing_definitions' => array(
            ActionWithOptionsMetadata::class,
            WatchRoutingDefinitionsAction::class,
            array(
                WatchRoutingDefinitionsOptions::class,
            ),
        ),
    );

    /**
     * @var ActionMetadata[]
     */
    private $actionsMetadata;

    private static $instance;

    /**
     * @internal
     */
    public function __construct()
    {
        foreach (self::CORE_ACTIONS as $shortName => list($actionMetadataClass, $actionClass, $args)) {
            $this->add(new $actionMetadataClass(new \ReflectionClass($actionClass), $shortName, ...$args));
        }
    }

    public static function getInstance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return ActionMetadata[]
     */
    public function all(): array
    {
        return $this->actionsMetadata;
    }

    public function add(ActionMetadata $actionMetadata): self
    {
        if (isset($this->actionsMetadata[$shortName = $actionMetadata->getShortName()])) {
            throw new \RuntimeException();
        }

        $this->actionsMetadata[$shortName] = $actionMetadata;

        return $this;
    }

    public function isCoreAction(string $shortName): bool
    {
        return isset(self::CORE_ACTIONS[$shortName]);
    }
}
