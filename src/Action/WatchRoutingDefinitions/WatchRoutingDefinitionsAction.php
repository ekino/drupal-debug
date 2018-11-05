<?php

namespace Ekino\Drupal\Debug\Action\WatchRoutingDefinitions;

use Drupal\Core\Routing\RouteBuilderInterface;
use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantAction;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Ekino\Drupal\Debug\Kernel\Event\AfterRequestPreHandleEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Resource\ResourcesFreshnessChecker;

class WatchRoutingDefinitionsAction extends AbstractFileBackendDependantAction implements EventSubscriberActionInterface
{
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
     * @param AfterRequestPreHandleEvent $event
     *
     * @throws NotSupportedException
     */
    public function process(AfterRequestPreHandleEvent $event)
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker($this->cacheFilePath, $this->resources);
        if ($resourcesFreshnessChecker->isFresh()) {
            return;
        }

        $container = $event->getContainer();
        if (!$container->has('router.builder')) {
            throw new NotSupportedException();
        }

        $routerBuilder = $container->get('router.builder');
        if (!$routerBuilder instanceof RouteBuilderInterface) {
            throw new NotSupportedException();
        }

        $routerBuilder->rebuild();

        $resourcesFreshnessChecker->commit();
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks()
    {
        return array(
            '%machine_name%.routing.yml',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultCacheFileName()
    {
        return 'routing.meta';
    }
}
