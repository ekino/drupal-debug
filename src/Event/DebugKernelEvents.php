<?php

namespace Ekino\Drupal\Debug\Event;

final class DebugKernelEvents
{
    /**
     * @var string
     */
    const ON_KERNEL_INSTANTIATION = 'ekino.drupal.debug.kernel.on_kernel_instantiation';

    /**
     * @var string
     */
    const AFTER_ENVIRONMENT_BOOT = 'ekino.drupal.debug.kernel.after_environment_boot';

    /**
     * @var string
     */
    const AFTER_SETTINGS_INITIALIZATION = 'ekino.drupal.debug.kernel.after_settings_initialization';

    /**
     * @var string
     */
    const AFTER_CONTAINER_INITIALIZATION = 'ekino.drupal.debug.kernel.after_container_initialization';

    /**
     * @var string
     */
    const AFTER_REQUEST_PRE_HANDLE = 'ekino.drupal.debug.kernel.after_request_pre_handle';
}
