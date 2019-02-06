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

namespace Ekino\Drupal\Debug\Tests\Integration\Drush;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Symfony\Component\Process\Process;

class DrushCacheRebuildTest extends AbstractTestCase
{
    public function test(): void
    {
        $this->useDebugKernel();

        // cf DrupalFinder\DrupalFinder::isValidRoot()
        $_ENV['COMPOSER'] = 'index.php';

        $this->assertSame(0, (new Process(\sprintf('%s/drush/drush/drush cr', self::VENDOR_DIRECTORY_PATH), self::DRUPAL_DIRECTORY_PATH))->run());
    }
}
