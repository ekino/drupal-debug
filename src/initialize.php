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

use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Kernel\Helper\OriginalDrupalKernelHelper;

if (!\function_exists('_drupal_debug_initialize')) {
    function _drupal_debug_initialize(): void
    {
        ConfigurationManager::initialize();

        $substituteOriginalDrupalKernelConfiguration = ConfigurationManager::getSubstituteOriginalDrupalKernelConfiguration();
        if ($substituteOriginalDrupalKernelConfiguration->isEnabled()) {
            OriginalDrupalKernelHelper::substitute($substituteOriginalDrupalKernelConfiguration->getClassLoader(), $substituteOriginalDrupalKernelConfiguration->getCacheDirectory());
        }
    }
}

if (\defined('PHPUNIT_COMPOSER_INSTALL') ||
    (false !== \getenv('DRUPAL_DEBUG_TESTS_ARE_RUNNING') && false === \getenv('DRUPAL_DEBUG_TESTS_FORCE_INITIALIZATION'))) {
    return;
}

\_drupal_debug_initialize();
