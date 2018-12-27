<?php

declare(strict_types=1);

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
    public function setUp()
    {
        $this->notSupportedException = new NotSupportedException('foo');
    }

    public function testInstanceOfException()
    {
        $this->assertInstanceOf(\Exception::class, $this->notSupportedException);
    }

    public function testGetMessage()
    {
        $this->assertSame('foo', $this->notSupportedException->getMessage());
    }
}
