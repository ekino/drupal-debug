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
