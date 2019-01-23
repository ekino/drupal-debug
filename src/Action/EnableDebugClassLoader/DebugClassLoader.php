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

namespace Ekino\Drupal\Debug\Action\EnableDebugClassLoader;

use Symfony\Component\Debug\DebugClassLoader as BaseDebugClassLoader;

class DebugClassLoader extends BaseDebugClassLoader
{
    /**
     * @var bool|null
     */
    private $isFinder = null;

    public function findFile(string $class): ?string
    {
        if (null === $this->isFinder) {
            $refl = new \ReflectionProperty(BaseDebugClassLoader::class, 'isFinder');
            $refl->setAccessible(true);

            $this->isFinder = $refl->getValue($this);
        }

        return $this->isFinder ? $this->getClassLoader()[0]->findFile($class) ?: null : null;
    }
}
