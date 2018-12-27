<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Kernel\Event;

class DebugKernelEvents
{
    /**
     * @var string
     */
    const ON_KERNEL_INSTANTIATION = 'ekino.drupal.debug.debug_kernel.on_kernel_instantiation';

    /**
     * @var string
     */
    const AFTER_ENVIRONMENT_BOOT = 'ekino.drupal.debug.debug_kernel.after_environment_boot';

    /**
     * @var string
     */
    const AFTER_SETTINGS_INITIALIZATION = 'ekino.drupal.debug.debug_kernel.after_settings_initialization';

    /**
     * @var string
     */
    const AFTER_ATTACH_SYNTHETIC = 'ekino.drupal.debug.debug_kernel.after_attach_synthetic';

    /**
     * @var string
     */
    const AFTER_CONTAINER_INITIALIZATION = 'ekino.drupal.debug.debug_kernel.after_container_initialization';

    /**
     * @var string
     */
    const AFTER_REQUEST_PRE_HANDLE = 'ekino.drupal.debug.debug_kernel.after_request_pre_handle';
}
