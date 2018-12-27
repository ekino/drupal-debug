<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Resource\Model;

use Carbon\Carbon;
use Ekino\Drupal\Debug\Extension\Model\CustomExtensionInterface;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Resource\Model\CustomExtensionFileResource;
use PHPUnit\Framework\TestCase;

class CustomExtensionFileResourceTest extends TestCase
{
    /**
     * @var string
     */
    const EXISTING_FILE_PATH = __DIR__.'/fixtures/__existing.php';

    /**
     * @var string
     */
    const NOT_EXISTING_FILE_PATH = __DIR__.'/fixtures/__not_existing.php';

    /**
     * @var CustomTheme
     */
    private $customExtension;

    /**
     * @var CustomExtensionFileResource
     */
    private $customExtensionFileResource;

    /**
     * @var string
     */
    private $serializedCustomExtensionFileResource;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        \touch(self::EXISTING_FILE_PATH);
        if (!\is_file(self::EXISTING_FILE_PATH)) {
            $this->markTestIncomplete(\sprintf('File "%s" could not be created.', self::EXISTING_FILE_PATH));
        }

        if (\is_file(self::NOT_EXISTING_FILE_PATH)) {
            if (!\unlink(self::NOT_EXISTING_FILE_PATH)) {
                $this->markTestIncomplete(\sprintf('File "%s" should not exists and could not be deleted.', self::NOT_EXISTING_FILE_PATH));
            }
        }

        $this->customExtension = new CustomTheme('/foo', 'bar');

        $this->customExtensionFileResource = new CustomExtensionFileResource(self::EXISTING_FILE_PATH, $this->customExtension);

        $customExtensionFileResourcePath = $this->customExtensionFileResource->getFilePath();
        $this->serializedCustomExtensionFileResource = \sprintf('a:3:{i:0;s:%s:"%s";i:1;C:46:"Ekino\Drupal\Debug\Extension\Model\CustomTheme":35:{a:2:{i:0;s:4:"/foo";i:1;s:3:"bar";}}i:2;b:1;}', \mb_strlen($customExtensionFileResourcePath), $customExtensionFileResourcePath);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (\is_file(self::EXISTING_FILE_PATH)) {
            \unlink(self::EXISTING_FILE_PATH);
        }

        if (\is_file(self::NOT_EXISTING_FILE_PATH)) {
            \unlink(self::NOT_EXISTING_FILE_PATH);
        }
    }

    public function testToString()
    {
        $this->assertSame(self::EXISTING_FILE_PATH, $this->customExtensionFileResource->__toString());
    }

    public function testGetFilePath()
    {
        $this->assertSame(self::EXISTING_FILE_PATH, $this->customExtensionFileResource->getFilePath());
    }

    public function testGetCustomExtension()
    {
        $this->assertSame($this->customExtension, $this->customExtensionFileResource->getCustomExtension());
    }

    /**
     * @dataProvider isFreshProvider
     */
    public function testIsFresh($expected, $existsNow, $existed, $filemtime = null, $timestamp = null)
    {
        $customExtensionFileResource = $this->getContextualCustomExtensionFileResource($existsNow, $existed);

        if (\is_int($filemtime)) {
            $filePath = $customExtensionFileResource->getFilePath();
            if (!\touch($filePath, $filemtime)) {
                $this->markTestIncomplete(\sprintf('File "%s" could not be touched.', $filePath));
            }

            \clearstatcache();
        }

        $this->assertSame($expected, $customExtensionFileResource->isFresh($timestamp));
    }

    public function isFreshProvider()
    {
        $now = Carbon::now();
        $nowTs = $now->getTimestamp();
        $pastTs = $now->subSecond()->getTimestamp();
        $futureTs = $now->addSeconds(2)->getTimestamp();

        return array(
          array(true, false, false),
          array(false, false, true),
          array(false, true, false),
          array(true, true, true, $nowTs, $nowTs),
          array(true, true, true, $pastTs, $nowTs),
          array(false, true, true, $futureTs, $nowTs),
        );
    }

    /**
     * @dataProvider isNewProvider
     */
    public function testIsNew($expected, $existed, $existsNow)
    {
        $customExtensionFileResource = $this->getContextualCustomExtensionFileResource($existsNow, $existed);

        $this->assertSame($expected, $customExtensionFileResource->isNew());
    }

    public function isNewProvider()
    {
        return array(
          array(true, false, true),
          array(false, false, false),
          array(false, true, true),
          array(false, true, false),
        );
    }

    public function testSerialize()
    {
        $this->assertSame($this->serializedCustomExtensionFileResource, $this->customExtensionFileResource->serialize());
    }

    public function testUnserialize()
    {
        $customExtensionFileResource = new CustomExtensionFileResource('foo', $this->createMock(CustomExtensionInterface::class));
        $customExtensionFileResource->unserialize($this->serializedCustomExtensionFileResource);

        $this->assertEquals($this->customExtensionFileResource, $customExtensionFileResource);
    }

    /**
     * @param bool $existsNow
     * @param bool $existed
     *
     * @return CustomExtensionFileResource
     */
    private function getContextualCustomExtensionFileResource($existsNow, $existed)
    {
        $filePath = $existed ? self::EXISTING_FILE_PATH : self::NOT_EXISTING_FILE_PATH;

        $customExtensionFileResource = new CustomExtensionFileResource($filePath, $this->customExtension);

        if (!$existed && $existsNow) {
            \touch($filePath);
            if (!\is_file($filePath)) {
                $this->markTestIncomplete(\sprintf('File "%s" could not be created.', $filePath));
            }
        } elseif ($existed && !$existsNow) {
            \unlink($filePath);
            if (\is_file($filePath)) {
                $this->markTestIncomplete(\sprintf('File "%s" could not be deleted.', $filePath));
            }
        }

        return $customExtensionFileResource;
    }
}
