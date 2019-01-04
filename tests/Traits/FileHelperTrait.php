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

use PHPUnit\Framework\IncompleteTestError;

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
                throw new IncompleteTestError(\sprintf('The file "%s" should not exist and could not be deleted.', $path));
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
            throw new IncompleteTestError(\sprintf('The content of the file "%s" could not be gotten.', $path));
        }

        return $content;
    }

    /**
     * @param string $path
     * @param string $content
     */
    private static function writeFile(string $path, string $content): void
    {
        if (!\file_put_contents($path, $content)) {
            throw new IncompleteTestError(\sprintf('The file "%s" content could not be written.', $path));
        }
    }

    /**
     * @param string $path
     * @param int    $timestamp
     */
    private static function touch(string $path, int $timestamp): void
    {
        if (!\touch($path, $timestamp)) {
            throw new IncompleteTestError(\sprintf('The file "%s" could not be touched.', $path));
        }

        \clearstatcache();
    }
}
