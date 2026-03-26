<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ilbee\Okta\Event\DTO\OktaActor;
use Ilbee\Okta\Event\DTO\OktaEvent;
use Ilbee\Okta\Event\DTO\OktaTarget;
use Ilbee\Okta\Event\Event\AbstractOktaEvent;
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserAssignedEvent;
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserUnassignedEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberAddedEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberRemovedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserActivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserCreatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeletedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserPasswordResetEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserProfileUpdatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserReactivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserSuspendedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserUnsuspendedEvent;
use Ilbee\Okta\Event\Service\OktaEventMapper;

class OktaEventMapperTest extends TestCase
{
    private OktaEventMapper $mapper;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mapper = new OktaEventMapper($this->logger);
    }

    #[DataProvider('supportedEventTypesProvider')]
    public function testSupportsReturnsTrueForMappedEventTypes(string $eventType): void
    {
        self::assertTrue($this->mapper->supports($eventType));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function supportedEventTypesProvider(): iterable
    {
        yield 'activate' => ['user.lifecycle.activate'];
        yield 'create' => ['user.lifecycle.create'];
        yield 'deactivate' => ['user.lifecycle.deactivate'];
        yield 'delete' => ['user.lifecycle.delete.initiated'];
        yield 'reactivate' => ['user.lifecycle.reactivate'];
        yield 'suspend' => ['user.lifecycle.suspend'];
        yield 'unsuspend' => ['user.lifecycle.unsuspend'];
        yield 'password_reset' => ['user.lifecycle.password_reset'];
        yield 'profile_updated' => ['user.account.update_profile'];
        yield 'group_add' => ['group.user_membership.add'];
        yield 'group_remove' => ['group.user_membership.remove'];
        yield 'app_add' => ['application.user_membership.add'];
        yield 'app_remove' => ['application.user_membership.remove'];
    }

    public function testSupportsReturnsFalseForUnknownEventType(): void
    {
        self::assertFalse($this->mapper->supports('system.org.rate_limit.warning'));
        self::assertFalse($this->mapper->supports('unknown.event'));
    }

    /**
     * @param class-string<AbstractOktaEvent> $expectedClass
     */
    #[DataProvider('lifecycleEventMappingProvider')]
    public function testMapReturnsCorrectLifecycleEventClass(string $eventType, string $expectedClass): void
    {
        $oktaEvent = $this->buildOktaEvent($eventType, [$this->buildUserTarget()]);

        $result = $this->mapper->map($oktaEvent);

        self::assertInstanceOf($expectedClass, $result);
        self::assertSame('user@example.com', $result->userEmail);
        self::assertSame($eventType, $result->eventType);
        self::assertSame('00u1234567890', $result->target->id);
        self::assertSame('00uadmin123', $result->actor->id);
    }

    /**
     * @return iterable<string, array{string, class-string<AbstractOktaEvent>}>
     */
    public static function lifecycleEventMappingProvider(): iterable
    {
        yield 'activate' => ['user.lifecycle.activate', OktaUserActivatedEvent::class];
        yield 'create' => ['user.lifecycle.create', OktaUserCreatedEvent::class];
        yield 'deactivate' => ['user.lifecycle.deactivate', OktaUserDeactivatedEvent::class];
        yield 'delete' => ['user.lifecycle.delete.initiated', OktaUserDeletedEvent::class];
        yield 'reactivate' => ['user.lifecycle.reactivate', OktaUserReactivatedEvent::class];
        yield 'suspend' => ['user.lifecycle.suspend', OktaUserSuspendedEvent::class];
        yield 'unsuspend' => ['user.lifecycle.unsuspend', OktaUserUnsuspendedEvent::class];
        yield 'password_reset' => ['user.lifecycle.password_reset', OktaUserPasswordResetEvent::class];
        yield 'profile_updated' => ['user.account.update_profile', OktaUserProfileUpdatedEvent::class];
    }

    public function testMapExtractsGroupDataForGroupMemberAddedEvent(): void
    {
        $oktaEvent = $this->buildOktaEvent('group.user_membership.add', [
            $this->buildUserTarget(),
            $this->buildGroupTarget(),
        ]);

        $result = $this->mapper->map($oktaEvent);

        self::assertInstanceOf(OktaGroupMemberAddedEvent::class, $result);
        self::assertSame('user@example.com', $result->userEmail);
        self::assertSame('00gGroup123', $result->groupId);
        self::assertSame('Engineering', $result->groupName);
    }

    public function testMapExtractsGroupDataForGroupMemberRemovedEvent(): void
    {
        $oktaEvent = $this->buildOktaEvent('group.user_membership.remove', [
            $this->buildUserTarget(),
            $this->buildGroupTarget(),
        ]);

        $result = $this->mapper->map($oktaEvent);

        self::assertInstanceOf(OktaGroupMemberRemovedEvent::class, $result);
        self::assertSame('00gGroup123', $result->groupId);
        self::assertSame('Engineering', $result->groupName);
    }

    public function testMapExtractsAppDataForAppUserAssignedEvent(): void
    {
        $oktaEvent = $this->buildOktaEvent('application.user_membership.add', [
            $this->buildUserTarget(),
            $this->buildAppTarget(),
        ]);

        $result = $this->mapper->map($oktaEvent);

        self::assertInstanceOf(OktaAppUserAssignedEvent::class, $result);
        self::assertSame('user@example.com', $result->userEmail);
        self::assertSame('0oaApp456', $result->appId);
        self::assertSame('Slack', $result->appName);
    }

    public function testMapExtractsAppDataForAppUserUnassignedEvent(): void
    {
        $oktaEvent = $this->buildOktaEvent('application.user_membership.remove', [
            $this->buildUserTarget(),
            $this->buildAppTarget(),
        ]);

        $result = $this->mapper->map($oktaEvent);

        self::assertInstanceOf(OktaAppUserUnassignedEvent::class, $result);
        self::assertSame('0oaApp456', $result->appId);
        self::assertSame('Slack', $result->appName);
    }

    public function testMapReturnsNullWhenUserTargetHasNonEmailAlternateId(): void
    {
        $this->logger->expects(self::once())
            ->method('warning')
            ->with('Skipping target with non-email alternateId.', self::callback(static fn (array $context): bool => 'not-an-email' === $context['alternateId']));

        $target = new OktaTarget('not-an-email', '00u123', 'User', 'John Doe');
        $oktaEvent = $this->buildOktaEvent('user.lifecycle.deactivate', [$target]);

        $result = $this->mapper->map($oktaEvent);

        self::assertNull($result);
    }

    public function testMapReturnsNullWhenNoUserTargetFound(): void
    {
        $this->logger->expects(self::once())
            ->method('warning')
            ->with('No User target found in event.', self::anything());

        $target = new OktaTarget('engineering', '00gGroup123', 'UserGroup', 'Engineering');
        $oktaEvent = $this->buildOktaEvent('group.user_membership.add', [$target]);

        $result = $this->mapper->map($oktaEvent);

        self::assertNull($result);
    }

    public function testMapHandlesMissingGroupTargetGracefully(): void
    {
        $this->logger->expects(self::once())
            ->method('warning')
            ->with('No UserGroup target found in group membership event.', self::anything());

        $oktaEvent = $this->buildOktaEvent('group.user_membership.add', [
            $this->buildUserTarget(),
        ]);

        $result = $this->mapper->map($oktaEvent);

        self::assertNull($result);
    }

    public function testMapHandlesMissingAppTargetGracefully(): void
    {
        $this->logger->expects(self::once())
            ->method('warning')
            ->with('No AppInstance target found in app assignment event.', self::anything());

        $oktaEvent = $this->buildOktaEvent('application.user_membership.add', [
            $this->buildUserTarget(),
        ]);

        $result = $this->mapper->map($oktaEvent);

        self::assertNull($result);
    }

    public function testMapReturnsNullForUnsupportedEventType(): void
    {
        $oktaEvent = $this->buildOktaEvent('system.org.rate_limit.warning', [$this->buildUserTarget()]);

        $result = $this->mapper->map($oktaEvent);

        self::assertNull($result);
    }

    /**
     * @param OktaTarget[] $targets
     */
    private function buildOktaEvent(string $eventType, array $targets): OktaEvent
    {
        $actor = new OktaActor('00uadmin123', 'User', 'admin@example.com', 'Admin User');

        return new OktaEvent($eventType, $targets, $actor);
    }

    private function buildUserTarget(): OktaTarget
    {
        return new OktaTarget('user@example.com', '00u1234567890', 'User', 'John Doe');
    }

    private function buildGroupTarget(): OktaTarget
    {
        return new OktaTarget('engineering', '00gGroup123', 'UserGroup', 'Engineering');
    }

    private function buildAppTarget(): OktaTarget
    {
        return new OktaTarget('Slack', '0oaApp456', 'AppInstance', 'Slack');
    }
}
