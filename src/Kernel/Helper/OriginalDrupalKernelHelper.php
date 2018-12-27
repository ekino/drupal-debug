<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Kernel\Helper;

use Composer\Autoload\ClassLoader;
use Drupal\Core\DrupalKernel;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Ekino\Drupal\Debug\Kernel\DebugKernel;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Ekino\Drupal\Debug\Resource\ResourcesFreshnessChecker;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This helper substitutes entirely the original DrupalKernel with the
 * DebugKernel.
 *
 * The substitution is done with a class_alias.
 *
 * An original DrupalKernel substitute with a dedicated name
 * (OriginalDrupalKernel) must be created because the DebugKernel cannot
 * technically substitute the "\Drupal\Core\DrupalKernel" class and extends it
 * at the same time.
 *
 * The original DrupalKernel and the DebugKernel classes must not be loaded
 * before the alias declaration.
 */
class OriginalDrupalKernelHelper
{
    /**
     * @param ClassLoader $classLoader
     * @param string      $cacheDirectory
     */
    public static function substitute(ClassLoader $classLoader, $cacheDirectory)
    {
        $originalDrupalKernelFilePath = $classLoader->findFile('Drupal\Core\DrupalKernel');
        if (!\is_string($originalDrupalKernelFilePath)) {
            throw new \RuntimeException('The original DrupalKernel class file could not be found.');
        }

        $originalDrupalKernelSubstituteFilePath = \sprintf('%s/OriginalDrupalKernel.php', $cacheDirectory);
        // We watch the original DrupalKernel and the DebugKernel : if they did
        // not change, then the original DrupalKernel substitute does not need
        // to be recreated.
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(\sprintf('%s.meta', $originalDrupalKernelSubstituteFilePath), new ResourcesCollection(array(
            new FileExistenceResource($originalDrupalKernelSubstituteFilePath),
            new FileResource($originalDrupalKernelFilePath),
            new FileResource(\sprintf('%s/../DebugKernel.php', __DIR__)),
        )));

        if (!$resourcesFreshnessChecker->isFresh()) {
            self::createOriginalDrupalKernelSubstitute($originalDrupalKernelFilePath, $originalDrupalKernelSubstituteFilePath);

            $resourcesFreshnessChecker->commit();
        }

        require $originalDrupalKernelSubstituteFilePath;

        if (!@\class_alias(DebugKernel::class, DrupalKernel::class)) {
            throw new \RuntimeException('The DebugKernel class could not be aliased.');
        }
    }

    /**
     * @param string $originalDrupalKernelFilePath
     * @param string $originalDrupalKernelSubstituteFilePath
     */
    private static function createOriginalDrupalKernelSubstitute($originalDrupalKernelFilePath, $originalDrupalKernelSubstituteFilePath)
    {
        $content = @\file_get_contents($originalDrupalKernelFilePath);
        if (false === $content) {
            throw new \RuntimeException('The original DrupalKernel content could not be read.');
        }

        $content = \preg_replace(array(
            '/^class DrupalKernel/m',
            '/__DIR__/',
        ), array(
            'class OriginalDrupalKernel',
            \sprintf("'%s'", \dirname($originalDrupalKernelFilePath)),
        ), $content, -1, $replacementsDoneCount);
        if (null === $content) {
            throw new \RuntimeException('The original DrupalKernel specific values could not be replaced.');
        }

        // There should be only 2 replacements :
        //   * The class name
        //   * And the __DIR__ in the ::guessApplicationRoot() method
        //
        // If there is another replacement, we cannot support it naively. For
        // safety, it has to be reviewed.
        if (2 !== $replacementsDoneCount) {
            throw new NotSupportedException('There should be strictly 2 replacements done in the original DrupalKernel substitute.');
        }

        (new Filesystem())->dumpFile($originalDrupalKernelSubstituteFilePath, $content);
    }
}
