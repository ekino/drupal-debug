<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\EnableDebugClassLoader;

use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\Debug\DebugClassLoader;

class EnableDebugClassLoaderAction implements EventSubscriberActionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::ON_KERNEL_INSTANTIATION => 'process',
        );
    }

    public function process()
    {
        DebugClassLoader::enable();
    }
}
