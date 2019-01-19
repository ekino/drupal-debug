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

namespace Ekino\Drupal\Debug\Tests\Integration\DisableTwigCache;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Symfony\Component\BrowserKit\Client;

class DisableTwigCacheTest extends AbstractTestCase
{
    use FileHelperTrait;

    /**
     * @var string
     */
    private const PARTIAL_FILE_PATH = __DIR__.'/fixtures/modules/use_twig_template/templates/--partial.html.twig';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->deletePartialFile();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deletePartialFile(false);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        foreach ($this->executeScenario($client) as $result) {
            $this->assertContains('I love to eat apples!', $result);
            $this->assertNotContains('I prefer pears.', $result);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $results = $this->executeScenario($client);

        $this->assertContains('I love to eat apples!', $results[0]);
        $this->assertNotContains('I prefer pears.', $results[0]);

        $this->assertContains('I prefer pears.', $results[1]);
        $this->assertNotContains('I love to eat apples!', $results[1]);
    }

    private function executeScenario(Client $client): array
    {
        $results = array();

        foreach (array(
            '/bar' => 'I love to eat apples!',
            '/ccc' => 'I prefer pears.',
        ) as $uri => $content) {
            self::writeFile(self::PARTIAL_FILE_PATH, $content);

            $results[] = $client->request('GET', $uri)->text();
        }

        return $results;
    }

    private function deletePartialFile(bool $mandatory = true): void
    {
        self::deleteFile(self::PARTIAL_FILE_PATH, $mandatory);
    }
}
