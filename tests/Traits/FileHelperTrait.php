<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Traits;

use PHPUnit\Framework\IncompleteTestError;

trait FileHelperTrait
{
    /**
     * @param string $path
     * @param bool   $mandatory
     */
    private static function deleteFile($path, $mandatory = false)
    {
        if (\is_file($path)) {
            if (!\unlink($path) && $mandatory) {
                throw new IncompleteTestError(\sprintf('The file "%s" should not exist and could not be deleted.', $path));
            }
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private static function getFileContent($path)
    {
        $content = \file_get_contents($path);
        if (!\is_string($content)) {
            throw new IncompleteTestError(\sprintf('The content of the file "%s" could not be gotten.', $path));
        }

        return $content;
    }

    /**
     * @param string $path
     * @param string $content
     */
    private static function writeFile($path, $content)
    {
        if (!\file_put_contents($path, $content)) {
            throw new IncompleteTestError(\sprintf('The file "%s" content could not be written.', $path));
        }
    }

    /**
     * @param string $path
     * @param int    $timestamp
     */
    private static function touch($path, $timestamp)
    {
        if (!\touch($path, $timestamp)) {
            throw new IncompleteTestError(\sprintf('The file "%s" could not be touched.', $path));
        }

        \clearstatcache();
    }
}
