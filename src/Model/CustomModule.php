<?php

namespace Ekino\Drupal\Debug\Model;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Ekino\Drupal\Debug\Action\EnhanceClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnhanceContainerAction;
use Ekino\Drupal\Debug\Action\EnhanceDumpAction;
use Ekino\Drupal\Debug\Action\EnhanceExceptionPageAction;

class CustomModule extends AbstractCustomExtension
{
    /**
     * @var string
     */
    private $camelCaseMachineName;

    /**
     * {@inheritdoc}
     */
    public function __construct($rootPath, $machineName)
    {
        parent::__construct($rootPath, $machineName);

        // The same camelize function is used in the Drupal kernel.
        $this->camelCaseMachineName = ContainerBuilder::camelize($machineName);
    }

    /**
     * @return string
     */
    public function getCamelCaseMachineName()
    {
        return $this->camelCaseMachineName;
    }
}
