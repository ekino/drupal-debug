<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

interface ActionWithOptionsInterface extends ActionInterface
{
    /**
     * @return string
     */
    public static function getOptionsClass();
}
