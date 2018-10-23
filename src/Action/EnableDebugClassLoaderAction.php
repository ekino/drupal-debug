<?php

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\Event\DebugKernelEvents;
use Symfony\Component\Debug\DebugClassLoader;

class EnableDebugClassLoaderAction implements EventSubscriberActionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::ON_KERNEL_INSTANTIATION => 'process'
        );
    }

    public function process()
    {
        DebugClassLoader::enable();
    }

    /**
     * @param string $appRoot
     *
     * @return UseDebugClassLoaderAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self();
    }
}
