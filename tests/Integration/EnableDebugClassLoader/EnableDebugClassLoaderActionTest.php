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

namespace Ekino\Drupal\Debug\Tests\Integration\EnableDebugClassLoader;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Symfony\Component\BrowserKit\Client;

class EnableDebugClassLoaderActionTest extends AbstractTestCase
{
    /**
     * @var string
     */
    private const MODULE_SRC_DIRECTORY_PATH = __DIR__.'/fixtures/modules/use_invalid_class_name/src/';

    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $this->markTestIncomplete();
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $this->assertContains(\sprintf('Case mismatch between class and real file names: "Service/BarService.php" vs "Service/barService.php" in "%s".', self::MODULE_SRC_DIRECTORY_PATH), $client->request('GET', '/')->text());
    }
}
