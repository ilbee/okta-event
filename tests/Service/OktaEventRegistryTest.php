<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ilbee\Okta\Event\DTO\OktaEvent;
use Ilbee\Okta\Event\Event\AccessRequest\OktaAccessRequestCreatedEvent;
use Ilbee\Okta\Event\Event\AccessRequest\OktaAccessRequestEvent;
use Ilbee\Okta\Event\Event\GenericOktaEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupProfileUpdatedEvent;
use Ilbee\Okta\Event\Event\Security\OktaBreachedCredentialDetectedEvent;
use Ilbee\Okta\Event\Event\Security\OktaSecurityEvent;
use Ilbee\Okta\Event\Event\UserAuth\OktaUserAuthEvent;
use Ilbee\Okta\Event\Event\UserAuth\OktaUserSessionStartedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserLifecycleGroupEvent;
use Ilbee\Okta\Event\Service\OktaEventRegistry;

class OktaEventRegistryTest extends TestCase
{
    private OktaEventRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new OktaEventRegistry();
    }

    /**
     * @param class-string<GenericOktaEvent> $expectedClass
     */
    #[DataProvider('individualEventProvider')]
    public function testCreateIndividualEventReturnsCorrectClass(string $eventType, string $expectedClass): void
    {
        $oktaEvent = new OktaEvent($eventType);

        $result = $this->registry->createIndividualEvent($oktaEvent);

        self::assertNotNull($result);
        self::assertInstanceOf($expectedClass, $result);
        self::assertSame($oktaEvent, $result->oktaEvent);
    }

    /**
     * @return iterable<string, array{string, class-string<GenericOktaEvent>}>
     */
    public static function individualEventProvider(): iterable
    {
        yield 'access_request_create' => ['access.request.create', OktaAccessRequestCreatedEvent::class];
        yield 'group_profile_update' => ['group.profile.update', OktaGroupProfileUpdatedEvent::class];
        yield 'breached_credential' => ['security.breached_credential.detected', OktaBreachedCredentialDetectedEvent::class];
        yield 'user_session_start' => ['user.session.start', OktaUserSessionStartedEvent::class];
    }

    /**
     * @param class-string<GenericOktaEvent> $expectedClass
     */
    #[DataProvider('groupEventProvider')]
    public function testCreateGroupEventReturnsCorrectClass(string $eventType, string $expectedClass): void
    {
        $oktaEvent = new OktaEvent($eventType);

        $result = $this->registry->createGroupEvent($oktaEvent);

        self::assertNotNull($result);
        self::assertInstanceOf($expectedClass, $result);
        self::assertSame($oktaEvent, $result->oktaEvent);
    }

    /**
     * @return iterable<string, array{string, class-string<GenericOktaEvent>}>
     */
    public static function groupEventProvider(): iterable
    {
        yield 'access_request' => ['access.request.create', OktaAccessRequestEvent::class];
        yield 'group' => ['group.user_membership.add', OktaGroupEvent::class];
        yield 'security' => ['security.breached_credential.detected', OktaSecurityEvent::class];
        yield 'user_auth' => ['user.session.start', OktaUserAuthEvent::class];
        yield 'user_lifecycle' => ['user.lifecycle.deactivate', OktaUserLifecycleGroupEvent::class];
    }

    public function testCreateIndividualEventReturnsNullForUnknownType(): void
    {
        $oktaEvent = new OktaEvent('completely.unknown.event.type');

        self::assertNull($this->registry->createIndividualEvent($oktaEvent));
    }

    public function testCreateGroupEventReturnsNullForUnknownType(): void
    {
        $oktaEvent = new OktaEvent('completely.unknown.event.type');

        self::assertNull($this->registry->createGroupEvent($oktaEvent));
    }

    public function testIndividualEventExtendsGroupEvent(): void
    {
        $oktaEvent = new OktaEvent('access.request.create');

        $individual = $this->registry->createIndividualEvent($oktaEvent);
        $group = $this->registry->createGroupEvent($oktaEvent);

        self::assertNotNull($individual);
        self::assertNotNull($group);
        self::assertInstanceOf(OktaAccessRequestCreatedEvent::class, $individual);
        self::assertInstanceOf(OktaAccessRequestEvent::class, $group);
        /** @var array<string, class-string> $parents */
        $parents = class_parents($individual);
        self::assertArrayHasKey(OktaAccessRequestEvent::class, $parents, 'Individual event should extend the group event class');
    }

    public function testGetIndividualEventClassReturnsNullForTypedMapperEvents(): void
    {
        // user.lifecycle.activate is handled by OktaEventMapper, not the registry's individual map
        // But it may or may not be in the registry — this tests the boundary
        $class = $this->registry->getIndividualEventClass('user.lifecycle.activate');

        // This event type is NOT in INDIVIDUAL_EVENTS (handled by mapper), so should be null
        self::assertNull($class);
    }

    public function testGetGroupEventClassReturnsClassForTypedMapperEvents(): void
    {
        // user.lifecycle.activate IS in GROUP_EVENTS even though it's handled by mapper for typed dispatch
        $class = $this->registry->getGroupEventClass('user.lifecycle.activate');

        self::assertSame(OktaUserLifecycleGroupEvent::class, $class);
    }
}
