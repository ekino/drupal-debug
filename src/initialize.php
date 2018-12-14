<?php

use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Kernel\Helper\OriginalDrupalKernelHelper;

if (defined('PHPUNIT_COMPOSER_INSTALL') || false !== getenv('DRUPAL_DEBUG_TESTS_ARE_RUNNING')) {
    return;
}

ConfigurationManager::initialize();

$substituteOriginalDrupalKernelConfiguration = ConfigurationManager::getSubstituteOriginalDrupalKernelConfiguration();
if ($substituteOriginalDrupalKernelConfiguration->isEnabled()) {
    OriginalDrupalKernelHelper::substitute($substituteOriginalDrupalKernelConfiguration->getClassLoader(), $substituteOriginalDrupalKernelConfiguration->getCacheDirectory());
}
