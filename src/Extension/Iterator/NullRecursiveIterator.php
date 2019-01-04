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
