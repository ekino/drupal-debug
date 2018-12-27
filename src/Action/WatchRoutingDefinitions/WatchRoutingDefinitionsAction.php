<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\WatchRoutingDefinitions;

use Drupal\Core\Routing\RouteBuilderInterface;
use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Ekino\Drupal\Debug\Kernel\Event\AfterRequestPreHandleEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Resource\ResourcesFreshnessChecker;

class WatchRoutingDefinitionsAction implements EventSubscriberActionInterface, ActionWithOptionsInterface
{
    /**
     * @var WatchRoutingDefinitionsOptions
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_REQUEST_PRE_HANDLE => 'process',
        );
    }

    /**
     * @param WatchRoutingDefinitionsOptions $options
     */
    public function __construct(WatchRoutingDefinitionsOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @param AfterRequestPreHandleEvent $event
     */
    public function process(AfterRequestPreHandleEvent $event)
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker($this->options->getCacheFilePath(), $this->options->getFilteredResourcesCollection($event->getEnabledModules(), $event->getEnabledThemes()));
        if ($resourcesFreshnessChecker->isFresh()) {
            return;
        }

        $container = $event->getContainer();
        if (!$container->has('router.builder')) {
            throw new NotSupportedException('The "router.builder" service should already be set in the container.');
        }

        $routerBuilder = $container->get('router.builder');
        if (!$routerBuilder instanceof RouteBuilderInterface) {
            throw new NotSupportedException(\sprintf('The "router.builder" service class should implement the "%s" interface', RouteBuilderInterface::class));
        }

        $routerBuilder->rebuild();

        $resourcesFreshnessChecker->commit();
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass()
    {
        return WatchRoutingDefinitionsOptions::class;
    }
}
