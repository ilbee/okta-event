<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event;

use Ilbee\Okta\Event\DTO\OktaActor;
use Ilbee\Okta\Event\DTO\OktaTarget;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractOktaEvent extends Event
{
    public function __construct(
        public readonly string $userEmail,
        public readonly string $eventType,
        public readonly OktaTarget $target,
        public readonly ?OktaActor $actor,
    ) {
    }
}
