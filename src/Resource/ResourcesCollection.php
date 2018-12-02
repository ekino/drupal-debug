<?php

namespace Ekino\Drupal\Debug\Resource;

use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class ResourcesCollection implements \Countable, \Serializable
{
    /**
     * @var SelfCheckingResourceInterface[]
     */
    private $resources;

    /**
     * @param SelfCheckingResourceInterface[] $resources
     */
    public function __construct(array $resources = array())
    {
        $this->resources = $resources;
    }

    /**
     * @return SelfCheckingResourceInterface[]
     */
    public function all()
    {
        return $this->resources;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->resources = unserialize($serialized);
    }
}
