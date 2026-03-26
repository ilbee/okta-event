<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\UserApp;

use Ilbee\Okta\Event\DTO\OktaActor;
use Ilbee\Okta\Event\DTO\OktaTarget;
use Ilbee\Okta\Event\Event\AbstractOktaEvent;

final class OktaAppUserAssignedEvent extends AbstractOktaEvent
{
    public function __construct(
        string $userEmail,
        string $eventType,
        OktaTarget $target,
        ?OktaActor $actor,
        public readonly string $appId,
        public readonly ?string $appName,
    ) {
        parent::__construct($userEmail, $eventType, $target, $actor);
    }
}
