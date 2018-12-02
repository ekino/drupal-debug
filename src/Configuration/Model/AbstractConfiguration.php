<?php

namespace Ekino\Drupal\Debug\Configuration\Model;

abstract class AbstractConfiguration implements \Serializable
{
    /**
     * @var array
     */
    protected $processedConfiguration;

    /**
     * @param array $processedConfiguration
     */
    public function __construct(array $processedConfiguration)
    {
        $this->processedConfiguration = $processedConfiguration;
    }
}
