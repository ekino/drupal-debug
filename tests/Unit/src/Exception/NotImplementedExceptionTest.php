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

namespace Ekino\Drupal\Debug\Tests\Unit\Exception;

use Ekino\Drupal\Debug\Exception\NotImplementedException;
use PHPUnit\Framework\TestCase;

class NotImplementedExceptionTest extends TestCase
{
    /**
     * @var NotImplementedException
     */
    private $notImplementedException;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->notImplementedException = new NotImplementedException('foo');
    }

    public function testInstanceOfException(): void
    {
        $this->assertInstanceOf(\Exception::class, $this->notImplementedException);
    }

    public function testGetMessage(): void
    {
        $this->assertSame('foo', $this->notImplementedException->getMessage());
    }
}
