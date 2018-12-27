<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface EventSubscriberActionInterface extends ActionInterface, EventSubscriberInterface
{
}
