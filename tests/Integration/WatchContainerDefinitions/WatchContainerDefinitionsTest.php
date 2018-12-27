<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Integration\WatchContainerDefinitions;

use Carbon\Carbon;
use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Symfony\Component\BrowserKit\Client;

class WatchContainerDefinitionsTest extends AbstractTestCase
{
    use FileHelperTrait;

    const SERVICES_TEMPLATE_FILE_PATH = __DIR__.'/fixtures/services_template.yml';

    const MODULE_SERVICES_FILE_PATH = __DIR__.'/fixtures/modules/use_custom_service/use_custom_service.services.yml';

    const SERVICE_PROVIDER_TEMPLATE_FILE_PATH = __DIR__.'/fixtures/ServiceProviderTemplate.php';

    const MODULE_SERVICE_PROVIDER_FILE_PATH = __DIR__.'/fixtures/modules/use_custom_service/src/UseCustomServiceServiceProvider.php';

    private static $servicesTemplateFileContent = null;

    private static $serviceProviderTemplateFileContent = null;

    protected function setUp()
    {
        parent::setUp();

        self::deleteFile(self::MODULE_SERVICES_FILE_PATH, true);
        self::deleteFile(self::MODULE_SERVICE_PROVIDER_FILE_PATH, true);
    }

    protected function tearDown()
    {
        parent::tearDown();

        self::deleteFile(self::MODULE_SERVICES_FILE_PATH);
        self::deleteFile(self::MODULE_SERVICE_PROVIDER_FILE_PATH);
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$servicesTemplateFileContent = self::getFileContent(self::SERVICES_TEMPLATE_FILE_PATH);
        self::$serviceProviderTemplateFileContent = self::getFileContent(self::SERVICE_PROVIDER_TEMPLATE_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client)
    {
        $this->assertContains('Drupal debug ==> No service message!!!', $client->request('GET', '/')->text());

        $this->writeServicesFile('Services file is added');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> No service message!!!', $text);
        $this->assertNotContains('Drupal debug ==> Services file is added', $text);

        $this->writeServicesFile('Services file is modified');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> No service message!!!', $text);
        $this->assertNotContains('Drupal debug ==> Services file is modified', $text);

        $this->writeServiceProviderFile('Service provider is added');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> No service message!!!', $text);
        $this->assertNotContains('Drupal debug ==> Services file is added', $text);

        $this->writeServiceProviderFile('Service provider is modified');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> No service message!!!', $text);
        $this->assertNotContains('Drupal debug ==> Services file is modified', $text);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client)
    {
        $this->assertContains('Drupal debug ==> No service message!!!', $client->request('GET', '/')->text());

        $this->writeServicesFile('Services file is added');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> Services file is added', $text);
        $this->assertNotContains('Drupal debug ==> No service message!!!', $text);

        $this->writeServicesFile('Services file is modified');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> Services file is modified', $text);
        $this->assertNotContains('Drupal debug ==> Services file is added', $text);

        $this->writeServiceProviderFile('Service provider is added');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> Service provider is added', $text);
        $this->assertNotContains('Drupal debug ==> Service files is modified', $text);

        $this->writeServiceProviderFile('Service provider is modified');
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> Service provider is modified', $text);
        $this->assertNotContains('Drupal debug ==> Service provided is added', $text);

        $this->deleteServiceProviderFile();
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> Services file is modified', $text);
        $this->assertNotContains('Drupal debug ==> Service provider is modified', $text);

        $this->deleteServicesFile();
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Drupal debug ==> No service message!!!', $text);
        $this->assertNotContains('Drupal debug ==> Services file is modified', $text);
    }

    private function writeServicesFile($message)
    {
        $this->writeTemplatedFile(self::MODULE_SERVICES_FILE_PATH, self::$servicesTemplateFileContent, $message);
    }

    private function writeServiceProviderFile($message)
    {
        $this->writeTemplatedFile(self::MODULE_SERVICE_PROVIDER_FILE_PATH, self::$serviceProviderTemplateFileContent, $message);
    }

    private function writeTemplatedFile($path, $content, $message)
    {
        $touch = \is_file($path);

        self::writeFile($path, \strtr($content, array(
            '%message%' => $message,
        )));

        if ($touch) {
            self::touch($path, Carbon::now()->addSecond()->getTimestamp());
        }
    }

    private function deleteServicesFile()
    {
        self::deleteFile(self::MODULE_SERVICES_FILE_PATH, true);
    }

    private function deleteServiceProviderFile()
    {
        self::deleteFile(self::MODULE_SERVICE_PROVIDER_FILE_PATH, true);
    }
}
