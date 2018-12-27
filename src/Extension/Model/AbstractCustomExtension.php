<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Extension\Model;

abstract class AbstractCustomExtension implements CustomExtensionInterface
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var string
     */
    protected $machineName;

    /**
     * @param string $rootPath
     * @param string $machineName
     */
    public function __construct($rootPath, $machineName)
    {
        $this->rootPath = $rootPath;
        $this->machineName = $machineName;
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @return string
     */
    public function getMachineName()
    {
        return $this->machineName;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return \serialize(array(
            $this->rootPath,
            $this->machineName,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->rootPath, $this->machineName) = \unserialize($serialized);
    }
}
