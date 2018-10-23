<?php

namespace Ekino\Drupal\Debug\Helper;

use Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator;
use Drupal\Core\Extension\ExtensionDiscovery;
use Ekino\Drupal\Debug\Action\EnhanceClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnhanceContainerAction;
use Ekino\Drupal\Debug\Action\EnhanceDumpAction;
use Ekino\Drupal\Debug\Action\EnhanceExceptionPageAction;
use Ekino\Drupal\Debug\Iterator\RecursiveCustomExtensionFilterIterator;
use Ekino\Drupal\Debug\Model\CustomModule;
use Ekino\Drupal\Debug\Model\CustomTheme;

class CustomExtensionHelper
{
    /**
     * @var string[]
     */
    const POSSIBLE_CUSTOM_MODULES_ROOT_PATHS = array(
        'modules',
        'sites/all/modules',
    );

    /**
     * @var string[]
     */
    const POSSIBLE_CUSTOM_THEMES_ROOT_PATHS = array(
        'themes',
    );

    /**
     * @var string
     */
    private $appRoot;

    /**
     * @var array
     */
    private static $cache = array(
        'module' => array(),
        'theme' => array(),
    );

    /**
     * @param string $appRoot
     */
    public function __construct($appRoot)
    {
        $this->appRoot = $appRoot;
    }

    /**
     * @return CustomModule[]
     */
    public function getCustomModules()
    {
        return $this->get('module');
    }

    /**
     * @return CustomTheme[]
     */
    public function getCustomThemes()
    {
        return $this->get('theme');
    }

    /**
     * @param string $type
     *
     * @return AbstractCustomExtension[]
     */
    private function get($type)
    {
        if (!isset(self::$cache[$type])) {
            throw new \InvalidArgumentException('The "%s" type is invalid.', $type);
        }

        if (!isset(self::$cache[$type][$this->appRoot])) {
            self::$cache[$type][$this->appRoot] = array();

            foreach ($this->getExistingRootPaths($type) as $existingCustomExtensionRootPath) {
                foreach ($this->searchRecursively($type, new \SplFileInfo($existingCustomExtensionRootPath)) as $customExtension) {
                    self::$cache[$type][$this->appRoot][] = $customExtension;
                }
            }
        }

        return self::$cache[$type][$this->appRoot];
    }

    /**
     * @param string $type
     *
     * @return string[]
     */
    private function getExistingRootPaths($type)
    {
        switch ($type) {
            case 'module':
                $possibleRootPaths = self::POSSIBLE_CUSTOM_MODULES_ROOT_PATHS;

                break;
            case 'theme':
                $possibleRootPaths = self::POSSIBLE_CUSTOM_THEMES_ROOT_PATHS;

                break;
            default:
                throw new \LogicException();
        }

        return array_filter(array_map(function ($possibleRootPath) {
            return sprintf('%s/%s', $this->appRoot, $possibleRootPath);
        }, $possibleRootPaths), 'is_dir');
    }

    /**
     * @param string $type
     * @param \SplFileInfo $splFileInfo
     *
     * @return AbstractCustomExtension[]
     */
    private function searchRecursively($type, \SplFileInfo $splFileInfo)
    {
        $customExtensions = array();

        $directoryIterator = new \RecursiveDirectoryIterator($splFileInfo->getRealPath(), \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_SELF);
        $filter = new RecursiveCustomExtensionFilterIterator($directoryIterator);
        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::LEAVES_ONLY, \RecursiveIteratorIterator::CATCH_GET_CHILD);
        foreach ($iterator as $splFileInfo) {
            $customExtensions[] = $this->create($type, $splFileInfo);
        }

        return $customExtensions;
    }

    /**
     * @param string $type
     * @param \SplFileInfo $splFileInfo
     *
     * @return AbstractCustomExtension
     */
    private function create($type, \SplFileInfo $splFileInfo)
    {
        switch ($type) {
            case 'module':
                return new CustomModule($splFileInfo->getPath(), $splFileInfo->getBasename('.info.yml'));
            case 'theme':
                return new CustomTheme($splFileInfo->getPath(), $splFileInfo->getBasename('.info.yml'));
            default:
                throw new \LogicException();
        }
    }
}
