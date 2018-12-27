<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Resource\Model;

use Ekino\Drupal\Debug\Extension\Model\CustomExtensionInterface;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class CustomExtensionFileResource implements SelfCheckingResourceInterface, \Serializable
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var CustomExtensionInterface
     */
    private $customExtension;

    /**
     * @var bool
     */
    private $existed;

    /**
     * @param string                   $filePath
     * @param CustomExtensionInterface $customExtension
     */
    public function __construct($filePath, CustomExtensionInterface $customExtension)
    {
        $this->filePath = $filePath;
        $this->customExtension = $customExtension;

        $this->existed = \is_file($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return CustomExtensionInterface
     */
    public function getCustomExtension()
    {
        return $this->customExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($timestamp)
    {
        if (!\is_file($this->filePath)) {
            return !$this->existed;
        } elseif (!$this->existed) {
            return false;
        }

        return false !== ($filemtime = @\filemtime($this->filePath)) && $filemtime <= $timestamp;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return false == $this->existed && \is_file($this->filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return \serialize(array(
            $this->filePath,
            $this->customExtension,
            $this->existed,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->filePath, $this->customExtension, $this->existed) = \unserialize($serialized);
    }
}
