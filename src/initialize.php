<?php

use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Kernel\Helper\OriginalDrupalKernelHelper;

ConfigurationManager::initialize();

$substituteOriginalDrupalKernelConfiguration = ConfigurationManager::getSubstituteOriginalDrupalKernelConfiguration();
if ($substituteOriginalDrupalKernelConfiguration->isEnabled()) {
    OriginalDrupalKernelHelper::substitute($substituteOriginalDrupalKernelConfiguration->getClassLoader(), $substituteOriginalDrupalKernelConfiguration->getCacheDirectory());
}
