<?php

namespace Ekino\Drupal\Debug\Action;

interface ActionInterface
{
    /**
     * @param string $appRoot
     *
     * @return self
     */
    public static function getDefaultAction($appRoot);
}
