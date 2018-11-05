<?php

namespace Ekino\Drupal\Debug\Extension\Model;

interface CustomExtensionInterface extends \Serializable
{
    /**
     * @return string
     */
    public function getRootPath();

    /**
     * @return string
     */
    public function getMachineName();
}
