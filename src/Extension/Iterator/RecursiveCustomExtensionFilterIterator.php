<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Extension\Iterator;

use Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator;

class RecursiveCustomExtensionFilterIterator extends \RecursiveFilterIterator
{
    /**
     * @var string[]
     */
    private $blacklist;

    /**
     * @param \RecursiveDirectoryIterator $iterator
     */
    public function __construct(\RecursiveDirectoryIterator $iterator)
    {
        parent::__construct($iterator);

        $drupalRecursiveExtensionFilterIterator = new RecursiveExtensionFilterIterator(new NullRecursiveIterator(), array(
            'tests',
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
        $current = $this->current();
        if (null === $current) {
            return false;
        }

        $fileName = $current->getFilename();
        if ('.' === $fileName[0]) {
            return false;
        }

        if (!$this->isDir()) {
            return '.info.yml' === \substr($fileName, -9);
        }

        if ('config' === $fileName) {
            return 'modules/config' === \substr($current->getPathname(), -14);
        }

        return !\in_array($fileName, $this->blacklist, true);
    }
}
