<?php

namespace Ekino\Drupal\Debug\Iterator;

use Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator;

class RecursiveCustomExtensionFilterIterator extends \RecursiveFilterIterator
{
    /**
     * @var string[]
     */
    private $blacklist;

    /**
     * RecursiveCustomExtensionFilterIterator constructor.
     *
     * @param \RecursiveIterator $iterator
     */
    public function __construct(\RecursiveIterator $iterator)
    {
        parent::__construct($iterator);

        $drupalRecursiveExtensionFilterIterator = new RecursiveExtensionFilterIterator(new NullRecursiveIterator(), array(
          'tests'
        ));
        $refl = new \ReflectionProperty($drupalRecursiveExtensionFilterIterator, 'blacklist');
        $refl->setAccessible(true);

        $this->blacklist = $refl->getValue($drupalRecursiveExtensionFilterIterator);
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        $name = $this->current()->getFilename();
        if ('.' === $name[0]) {
            return false;
        }

        if (!$this->isDir()) {
            return '.info.yml' === substr($name, -9);
        }

        if ('config' === $name) {
            return 'modules/config' === substr($this->current()->getPathname(), -14);
        }

        return !in_array($name, $this->blacklist, true);
    }
}
