<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Extension\Iterator;

class NullRecursiveIterator implements \RecursiveIterator
{
    /**
     * {@inheritdoc}
     */
    public function current()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
    }
}
