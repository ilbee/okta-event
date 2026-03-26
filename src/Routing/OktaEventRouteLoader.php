<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class OktaEventRouteLoader extends Loader
{
    private bool $loaded = false;

    public function __construct(
        private readonly string $routePath,
        private readonly string $controllerId,
    ) {
        parent::__construct();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->loaded) {
            throw new \RuntimeException('Do not add the "oktaevent_route_loader" loader twice.');
        }
        $this->loaded = true;

        $routes = new RouteCollection();

        $routes->add(
            'ilbee_okta_webhook',
            new Route(
                $this->routePath,
                ['_controller' => $this->controllerId],
                [],
                [],
                '',
                [],
                ['GET', 'POST']
            )
        );

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'oktaevent_route_loader' === $type;
    }
}
