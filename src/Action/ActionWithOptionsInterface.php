<?php

namespace Ekino\Drupal\Debug\Action;

interface ActionWithOptionsInterface extends ActionInterface
{
    /**
     * @return string
     */
    public static function getOptionsClass();
}
