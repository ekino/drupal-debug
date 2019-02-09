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
    private static function deleteFile(string $path, bool $mandatory = false): void
    {
        if (\is_file($path)) {
            if (!\unlink($path) && $mandatory) {
                throw new AssertionFailedError(\sprintf('The file "%s" should not exist and could not be deleted.', $path));
            }
        }
    }

    private static function getFileContent(string $path): string
    {
        $content = \file_get_contents($path);
        if (!\is_string($content)) {
            throw new AssertionFailedError(\sprintf('The content of the file "%s" could not be gotten.', $path));
        }

        return $content;
    }

    private static function writeFile(string $path, string $content, bool $append = false): void
    {
        if (false === \file_put_contents($path, $content, $append ? FILE_APPEND : 0)) {
            throw new AssertionFailedError(\sprintf('The file "%s" content could not be written.', $path));
        }
    }

    private static function touchFile(string $path, ?int $timestamp = null): void
    {
        $arguments = array($path);
        if (\is_int($timestamp)) {
            $arguments[] = $timestamp;
        }

        if (false === \call_user_func_array('touch', $arguments)) {
            throw new AssertionFailedError(\sprintf('The file "%s" could not be touched.', $path));
        }

        \clearstatcache();
    }

    private static function setFileNotWriteable(string $path): void
    {
        if (!\is_writable($path)) {
            return;
        }

        if (!\chmod($path, 0555)) {
            throw new AssertionFailedError(\sprintf('The path "%s" could not be made not writeable.', $path));
        }
    }

    private static function copyFile(string $source, string $dest): void
    {
        if (!\copy($source, $dest)) {
            throw new AssertionFailedError(\sprintf('The file "%s" could not be copied to "%s".', $source, $dest));
        }
    }
}
