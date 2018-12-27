<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Cache;

use Carbon\Carbon;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Ekino\Drupal\Debug\Resource\ResourcesFreshnessChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    /**
     * @var string
     */
    const NOT_EXISTING_FILE_PATH = __DIR__.'/fixtures/__not_existing.php';

    /**
     * @var string
     */
    const INVALID_FILE_PATH = __DIR__.'/fixtures/invalid.php';

    /**
     * @var string
     */
    const DATA_KEY_DOES_NOT_EXIST_FILE_PATH = __DIR__.'/fixtures/data_key_does_not_exist.php';

    /**
     * @var string
     */
    const NOT_AN_ARRAY_FILE_PATH = __DIR__.'/fixtures/not_an_array.php';

    /**
     * @var string
     */
    const VALID_FILE_PATH = __DIR__.'/fixtures/valid.php';

    /**
     * @var MockObject|ResourcesFreshnessChecker
     */
    private $resourcesFreshnessChecker;

    /**
     * @var FileCache
     */
    private $fileCache;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        if (\is_file(self::NOT_EXISTING_FILE_PATH)) {
            if (!\unlink(self::NOT_EXISTING_FILE_PATH)) {
                $this->markTestIncomplete(\sprintf('File "%s" should not exists and could not be deleted.', self::NOT_EXISTING_FILE_PATH));
            }
        }

        $this->fileCache = $this->getFileCache(self::VALID_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (\is_file(self::NOT_EXISTING_FILE_PATH)) {
            \unlink(self::NOT_EXISTING_FILE_PATH);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        Carbon::setTestNow(Carbon::createMidnightDate(2018, 11, 11));
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        Carbon::setTestNow(null);
    }

    /**
     * @dataProvider isFreshProvider
     */
    public function testIsFresh($expected, $isFresh)
    {
        $this->resourcesFreshnessChecker
            ->expects($this->atLeastOnce())
            ->method('isFresh')
            ->willReturn($isFresh);

        $this->assertSame($expected, $this->fileCache->isFresh());
    }

    public function isFreshProvider()
    {
        return array(
            array(false, false),
            array(true, true),
        );
    }

    public function testGetWhenTheFileDoesNotExists()
    {
        $fileCache = $this->getFileCache(self::NOT_EXISTING_FILE_PATH);

        $this->assertFalse($fileCache->get());
    }

    public function testGetWhenTheFileIsInvalid()
    {
        if (\PHP_VERSION_ID <= 70000) {
            $this->markTestSkipped('Requires PHP 7.');
        }

        $fileCache = $this->getFileCache(self::INVALID_FILE_PATH);

        $this->assertFalse($fileCache->get());
    }

    public function testGetWhenTheDataIsNotAnArray()
    {
        $fileCache = $this->getFileCache(self::NOT_AN_ARRAY_FILE_PATH);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The file cache data content should be an array.');

        $fileCache->get();
    }

    public function testGetWhenTheDataKeyDoesNotExists()
    {
        $fileCache = $this->getFileCache(self::DATA_KEY_DOES_NOT_EXIST_FILE_PATH);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The file cache data content should have a "data" key.');

        $fileCache->get();
    }

    public function testGet()
    {
        $fileCache = $this->getFileCache(self::VALID_FILE_PATH);

        $this->assertSame(array(
            'date' => '2018-11-09T10:00:43+00:00',
            'data' => array(
                'foo' => 'bar',
                'ccc' => array(
                    'fcy',
                ),
            ),
        ), $fileCache->get());
    }

    public function testGetDataWhenDataIsNotAnArray()
    {
        $fileCache = $this->getFileCache(self::NOT_EXISTING_FILE_PATH);

        $this->assertSame(array(), $fileCache->getData());
    }

    public function testGetData()
    {
        $fileCache = $this->getFileCache(self::VALID_FILE_PATH);

        $this->assertSame(array(
            'foo' => 'bar',
            'ccc' => array(
                'fcy',
            ),
        ), $fileCache->getData());
    }

    /**
     * @dataProvider writeProvider
     */
    public function testWrite(array $expected, array $currentData = null, array $dataToWrite)
    {
        if (\is_array($currentData)) {
            if (!\file_put_contents(self::NOT_EXISTING_FILE_PATH, '<?php return '.\var_export($currentData, true).';')) {
                $this->markTestIncomplete(\sprintf('File "%s" content could not be initialized.', self::NOT_EXISTING_FILE_PATH));
            }
        }

        $fileCache = $this->getFileCache(self::NOT_EXISTING_FILE_PATH);

        $this->resourcesFreshnessChecker
            ->expects($this->atLeastOnce())
            ->method('commit');

        $fileCache->write($dataToWrite);

        $this->assertSame($expected, $fileCache->get());
    }

    public function writeProvider()
    {
        $date = '2018-11-11T00:00:00+00:00';

        return array(
            array(
                array(
                    'date' => $date,
                    'data' => array(),
                ),
                null,
                array(),
            ),
            array(
                array(
                    'date' => $date,
                    'data' => array(),
                ),
                array(
                    'date' => 'foo',
                    'data' => array(),
                ),
                array(),
            ),
            array(
                array(
                    'date' => $date,
                    'data' => array(
                        'foo' => 'bar',
                    ),
                ),
                array(
                    'date' => 'foo',
                    'data' => array(),
                ),
                array(
                    'foo' => 'bar',
                ),
            ),
            array(
                array(
                    'date' => $date,
                    'data' => array(
                        'foo' => array(3, 4),
                    ),
                ),
                array(
                    'date' => 'foo',
                    'data' => array(
                        'foo' => array(1, 2),
                    ),
                ),
                array(
                    'foo' => array(3, 4),
                ),
            ),
            array(
                array(
                    'date' => $date,
                    'data' => array(
                        'foo' => 'bar',
                    ),
                ),
                array(
                    'date' => '2018-10-10T00:00:00+00:00',
                    'data' => array(
                        'foo' => 'bar',
                    ),
                ),
                array(
                    'foo' => 'bar',
                ),
            ),
        );
    }

    public function testInvalidate()
    {
        \touch(self::NOT_EXISTING_FILE_PATH);
        if (!\is_file(self::NOT_EXISTING_FILE_PATH)) {
            $this->markTestIncomplete(\sprintf('File "%s" could not be created.', self::NOT_EXISTING_FILE_PATH));
        }

        $fileCache = $this->getFileCache(self::NOT_EXISTING_FILE_PATH);

        $fileCache->invalidate();

        $this->assertFileNotExists(self::NOT_EXISTING_FILE_PATH);
    }

    public function testGetFilePath()
    {
        $this->assertSame(self::VALID_FILE_PATH, $this->fileCache->getFilePath());
    }

    /**
     * @param string $filePath
     *
     * @return FileCache
     */
    private function getFileCache($filePath)
    {
        $fileCache = new FileCache($filePath, $this->createMock(ResourcesCollection::class));

        $this->resourcesFreshnessChecker = $this->createMock(ResourcesFreshnessChecker::class);

        $refl = new \ReflectionProperty($fileCache, 'resourcesFreshnessChecker');
        $refl->setAccessible(true);
        $refl->setValue($fileCache, $this->resourcesFreshnessChecker);

        return $fileCache;
    }
}
