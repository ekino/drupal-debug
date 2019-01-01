<?php

declare(strict_types=1);

namespace Drupal\use_custom_route\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class FooController extends ControllerBase
{
    public function action(): Response
    {
        return new Response();
    }
}
