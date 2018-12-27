<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Helper;

use Drupal\Core\Site\Settings;
use Ekino\Drupal\Debug\Helper\SettingsHelper;
use PHPUnit\Framework\TestCase;

class SettingsHelperTest extends TestCase
{
    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var SettingsHelper
     */
    private $settingsHelper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->settingsHelper = new SettingsHelper();
    }

    public function testOverrideOnExistingKey()
    {
        new Settings(array(
            'foo' => 'bar',
            'one' => array(
                'two' => array(
                    'three' => 'val',
                ),
                'must' => 'not change',
            ),
        ));

        $this->settingsHelper->override('[foo]', 'ccc');
        $this->assertSame('ccc', Settings::get('foo'));

        $this->settingsHelper->override('[one][two][three]', 'php');
        $this->assertSame(array(
              'two' => array(
                    'three' => 'php',
              ),
              'must' => 'not change',
        ), Settings::get('one'));
    }

    public function testOverrideOnNewKey()
    {
        new Settings(array());

        $this->settingsHelper->override('[foo]', 'ccc');
        $this->assertSame('ccc', Settings::get('foo'));

        $this->settingsHelper->override('[one][two]', array(
            'foo' => 'php',
        ));
        $this->assertSame(array(
            'two' => array(
                'foo' => 'php',
            ),
        ), Settings::get('one'));
    }
}
