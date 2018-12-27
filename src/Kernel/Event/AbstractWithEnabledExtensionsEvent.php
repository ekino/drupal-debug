<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Kernel\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractWithEnabledExtensionsEvent extends Event
{
    /**
     * @var array
     */
    private $enabledModules;

    /**
     * @var array
     */
    private $enabledThemes;

    /**
     * @param array $enabledModules
     * @param array $enabledThemes
     */
    public function __construct(array $enabledModules, array $enabledThemes)
    {
        $this->enabledModules = $enabledModules;
        $this->enabledThemes = $enabledThemes;
    }

    /**
     * @return array
     */
    public function getEnabledModules()
    {
        return $this->enabledModules;
    }

    /**
     * @return array
     */
    public function getEnabledThemes()
    {
        return $this->enabledThemes;
    }
}
