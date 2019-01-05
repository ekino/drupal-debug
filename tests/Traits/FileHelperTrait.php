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

namespace Ekino\Drupal\Debug\Tests\Traits;

use PHPUnit\Framework\AssertionFailedError;

trait FileHelperTrait
{
    /**
     * @param string $path
     * @param bool   $mandatory
     */
    private static function deleteFile(string $path, bool $mandatory = false): void
    {
        if (\is_file($path)) {
            if (!\unlink($path) && $mandatory) {
                throw new AssertionFailedError(\sprintf('The file "%s" should not exist and could not be deleted.', $path));
            }
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private static function getFileContent(string $path): string
    {
        $content = \file_get_contents($path);
        if (!\is_string($content)) {
            throw new AssertionFailedError(\sprintf('The content of the file "%s" could not be gotten.', $path));
        }

        return $content;
    }

    /**
     * @param string $path
     * @param string $content
     */
    private static function writeFile(string $path, string $content): void
    {
        if (false === \file_put_contents($path, $content)) {
            throw new AssertionFailedError(\sprintf('The file "%s" content could not be written.', $path));
        }
    }

    /**
     * @param string $path
     * @param int    $timestamp
     */
    private static function touch(string $path, int $timestamp): void
    {
        if (!\touch($path, $timestamp)) {
            throw new AssertionFailedError(\sprintf('The file "%s" could not be touched.', $path));
        }

        \clearstatcache();
    }

    private static function setNotWriteable(string $path): void
    {
        if (!\is_writable($path)) {
            return;
        }

        if (!\chmod($path, 0555)) {
            throw new AssertionFailedError(\sprintf('The path "%s" could not be made not writeable.', $path));
        }
    }
}
