<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\Group;

use Ilbee\Okta\Event\DTO\OktaActor;
use Ilbee\Okta\Event\DTO\OktaTarget;
use Ilbee\Okta\Event\Event\AbstractOktaEvent;

final class OktaGroupMemberAddedEvent extends AbstractOktaEvent
{
    public function __construct(
        string $userEmail,
        string $eventType,
        OktaTarget $target,
        ?OktaActor $actor,
        public readonly string $groupId,
        public readonly ?string $groupName,
    ) {
        parent::__construct($userEmail, $eventType, $target, $actor);
    }
}
