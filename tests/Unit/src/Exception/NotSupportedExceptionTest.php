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

use Ekino\Drupal\Debug\Exception\NotSupportedException;
use PHPUnit\Framework\TestCase;

class NotSupportedExceptionTest extends TestCase
{
    /**
     * @var NotSupportedException
     */
    private $notSupportedException;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->notSupportedException = new NotSupportedException('foo');
    }

    public function testInstanceOfException(): void
    {
        $this->assertInstanceOf(\Exception::class, $this->notSupportedException);
    }

    public function testGetMessage(): void
    {
        $this->assertSame('foo', $this->notSupportedException->getMessage());
    }
}
