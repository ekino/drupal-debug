<?php

namespace Ekino\Drupal\Debug\Model;

abstract class AbstractCustomExtension
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $machineName;

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
}
