<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

interface CompilerPassActionInterface extends ActionInterface, CompilerPassInterface
{
}
