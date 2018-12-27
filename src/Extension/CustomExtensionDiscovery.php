<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Extension;

use Ekino\Drupal\Debug\Extension\Iterator\RecursiveCustomExtensionFilterIterator;
use Ekino\Drupal\Debug\Extension\Model\AbstractCustomExtension;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;

class CustomExtensionDiscovery
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
        /** @var CustomModule[] $customModules */
        $customModules = $this->get('module');

        return $customModules;
    }

    /**
     * @return CustomTheme[]
     */
    public function getCustomThemes()
    {
        /** @var CustomTheme[] $customThemes */
        $customThemes = $this->get('theme');

        return $customThemes;
    }

    /**
     * @param string $type
     *
     * @return CustomModule[]|CustomTheme[]
     */
    private function get($type)
    {
        if (!isset(self::$cache[$type])) {
            throw new \InvalidArgumentException(\sprintf('The "%s" type is invalid.', $type));
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
                throw new \LogicException('The type should be "module" or "theme".');
        }

        return \array_filter(\array_map(function ($possibleRootPath) {
            return \sprintf('%s/%s', $this->appRoot, $possibleRootPath);
        }, $possibleRootPaths), 'is_dir');
    }

    /**
     * @param string       $type
     * @param \SplFileInfo $splFileInfo
     *
     * @return AbstractCustomExtension[]
     */
    private function searchRecursively($type, \SplFileInfo $splFileInfo)
    {
        $customExtensions = array();

        $realPath = $splFileInfo->getRealPath();
        if (!\is_string($realPath)) {
            throw new \RuntimeException('The path should be a string.');
        }

        $directoryIterator = new \RecursiveDirectoryIterator($realPath, \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_SELF);
        $filter = new RecursiveCustomExtensionFilterIterator($directoryIterator);
        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::LEAVES_ONLY, \RecursiveIteratorIterator::CATCH_GET_CHILD);
        foreach ($iterator as $splFileInfo) {
            $customExtensions[] = $this->create($type, $splFileInfo);
        }

        return $customExtensions;
    }

    /**
     * @param string       $type
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
                throw new \LogicException('The type should be "module" or "theme".');
        }
    }
}
