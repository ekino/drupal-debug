<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Extension\Model;

use Drupal\Core\DependencyInjection\ContainerBuilder;

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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return \serialize(array(
            $this->rootPath,
            $this->machineName,
            $this->camelCaseMachineName,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->rootPath, $this->machineName, $this->camelCaseMachineName) = \unserialize($serialized);
    }
}
