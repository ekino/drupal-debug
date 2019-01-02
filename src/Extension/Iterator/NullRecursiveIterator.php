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
    public function next(): void
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
    public function valid(): bool
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren(): bool
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(): \RecursiveIterator
    {
    }
}
