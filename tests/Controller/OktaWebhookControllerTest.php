<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Ilbee\Okta\Event\Event\AbstractOktaEvent;
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserAssignedEvent;
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserUnassignedEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberAddedEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberRemovedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserActivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserLifecycleEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserPasswordResetEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserSuspendedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserUnsuspendedEvent;
use Ilbee\Okta\Event\Event\AccessRequest\OktaAccessRequestCreatedEvent;
use Ilbee\Okta\Event\Event\AccessRequest\OktaAccessRequestEvent;
use Ilbee\Okta\Event\Event\GenericOktaEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserLifecycleGroupEvent;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OktaWebhookControllerTest extends WebTestCase
{
    private const ACTOR = [
        'id' => '00uadmin123',
        'type' => 'User',
        'alternateId' => 'admin@example.com',
        'displayName' => 'Admin User',
    ];

    public function testGetVerificationChallengeSuccess(): void
    {
        $client = static::createClient();
        $challenge = 'some-random-challenge-string';

        $client->request(
            'GET',
            '/okta/webhook',
            [],
            [],
            ['HTTP_x-okta-verification-challenge' => $challenge]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = (string) $client->getResponse()->getContent();
        self::assertJson($responseContent);

        /** @var array<string, mixed> $decodedResponse */
        $decodedResponse = json_decode($responseContent, true);
        self::assertArrayHasKey('verification', $decodedResponse);
        self::assertSame($challenge, $decodedResponse['verification']);
    }

    public function testGetVerificationFailsWhenHeaderIsMissing(): void
    {
        $client = static::createClient();
        $client->request('GET', '/okta/webhook');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetVerificationFailsWhenChallengeFormatIsInvalid(): void
    {
        $client = static::createClient();
        $client->request('GET', '/okta/webhook', [], [], [
            'HTTP_x-okta-verification-challenge' => 'invalid challenge with spaces!',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostRequestFailsWithMissingAuthToken(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            [],
            '{"data":{"events":[]}}'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostRequestFailsWithInvalidAuthToken(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'wrong_secret'],
            '{"data":{"events":[]}}'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testPostRequestFailsWithMalformedPayload(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            '{"this is not valid json'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostValidDeactivateEventDispatchesTypedEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.deactivate'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertSame('Webhook processed.', $client->getResponse()->getContent());
    }

    public function testPostValidSuspendEventDispatchesTypedEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.suspend'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostValidActivateEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.activate'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostValidUnsuspendEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.unsuspend'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostValidPasswordResetEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.password_reset'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostGroupMemberAddedEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildGroupPayload('group.user_membership.add'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostGroupMemberRemovedEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildGroupPayload('group.user_membership.remove'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostAppUserAssignedEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildAppPayload('application.user_membership.add'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostAppUserUnassignedEvent(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildAppPayload('application.user_membership.remove'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostUnhandledEventTypeIsIgnored(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode($this->buildGenericPayload('completely.unknown.event'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostEmptyEventsReturnsOk(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            '{"data":{"events":[]}}'
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostTargetWithNonEmailAlternateIdIsSkipped(): void
    {
        $client = static::createClient();
        $payload = (string) json_encode([
            'data' => [
                'events' => [
                    [
                        'eventType' => 'user.lifecycle.deactivate',
                        'target' => [
                            [
                                'alternateId' => 'not-an-email',
                                'id' => '00u1234567890',
                                'type' => 'User',
                                'displayName' => 'John Doe',
                            ],
                        ],
                        'actor' => self::ACTOR,
                    ],
                ],
            ],
        ]);

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * @param class-string<AbstractOktaEvent> $expectedEventClass
     */
    #[DataProvider('typedEventDataProvider')]
    public function testTypedEventIsDispatched(string $eventType, string $expectedEventClass, callable $payloadBuilder): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener($expectedEventClass, static function (AbstractOktaEvent $event) use (&$dispatched): void {
            $dispatched[] = $event;
        });

        $payload = (string) json_encode($payloadBuilder($eventType));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $dispatched);
        self::assertInstanceOf($expectedEventClass, $dispatched[0]);
        self::assertSame('user@example.com', $dispatched[0]->userEmail);
        self::assertSame($eventType, $dispatched[0]->eventType);
    }

    /**
     * @return iterable<string, array{string, class-string<AbstractOktaEvent>, callable}>
     */
    public static function typedEventDataProvider(): iterable
    {
        $lifecycle = static fn (string $eventType): array => self::buildLifecyclePayloadStatic($eventType);
        $group = static fn (string $eventType): array => self::buildGroupPayloadStatic($eventType);
        $app = static fn (string $eventType): array => self::buildAppPayloadStatic($eventType);

        yield 'activate' => ['user.lifecycle.activate', OktaUserActivatedEvent::class, $lifecycle];
        yield 'deactivate' => ['user.lifecycle.deactivate', OktaUserDeactivatedEvent::class, $lifecycle];
        yield 'suspend' => ['user.lifecycle.suspend', OktaUserSuspendedEvent::class, $lifecycle];
        yield 'unsuspend' => ['user.lifecycle.unsuspend', OktaUserUnsuspendedEvent::class, $lifecycle];
        yield 'password_reset' => ['user.lifecycle.password_reset', OktaUserPasswordResetEvent::class, $lifecycle];
        yield 'group_add' => ['group.user_membership.add', OktaGroupMemberAddedEvent::class, $group];
        yield 'group_remove' => ['group.user_membership.remove', OktaGroupMemberRemovedEvent::class, $group];
        yield 'app_assign' => ['application.user_membership.add', OktaAppUserAssignedEvent::class, $app];
        yield 'app_unassign' => ['application.user_membership.remove', OktaAppUserUnassignedEvent::class, $app];
    }

    public function testDeactivateEventAlsoDispatchesDeprecatedLifecycleEvent(): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(OktaUserLifecycleEvent::class, static function (OktaUserLifecycleEvent $event) use (&$dispatched): void {
            $dispatched[] = $event;
        });

        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.deactivate'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $dispatched);
        self::assertSame('user@example.com', $dispatched[0]->userEmail);
        self::assertSame('user.lifecycle.deactivate', $dispatched[0]->eventType);
    }

    public function testSuspendEventAlsoDispatchesDeprecatedLifecycleEvent(): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(OktaUserLifecycleEvent::class, static function (OktaUserLifecycleEvent $event) use (&$dispatched): void {
            $dispatched[] = $event;
        });

        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.suspend'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $dispatched);
        self::assertSame('user@example.com', $dispatched[0]->userEmail);
        self::assertSame('user.lifecycle.suspend', $dispatched[0]->eventType);
    }

    public function testGroupEventContainsGroupData(): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(OktaGroupMemberAddedEvent::class, static function (OktaGroupMemberAddedEvent $event) use (&$dispatched): void {
            $dispatched[] = $event;
        });

        $payload = (string) json_encode($this->buildGroupPayload('group.user_membership.add'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $dispatched);
        self::assertSame('00gGroup123', $dispatched[0]->groupId);
        self::assertSame('Engineering', $dispatched[0]->groupName);
    }

    public function testAppEventContainsAppData(): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(OktaAppUserAssignedEvent::class, static function (OktaAppUserAssignedEvent $event) use (&$dispatched): void {
            $dispatched[] = $event;
        });

        $payload = (string) json_encode($this->buildAppPayload('application.user_membership.add'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $dispatched);
        self::assertSame('0oaApp456', $dispatched[0]->appId);
        self::assertSame('Slack', $dispatched[0]->appName);
    }

    public function testRegistryIndividualAndGroupEventsAreDispatchedForRegistryOnlyEvent(): void
    {
        $client = static::createClient();
        $individualDispatched = [];
        $groupDispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(OktaAccessRequestCreatedEvent::class, static function (OktaAccessRequestCreatedEvent $event) use (&$individualDispatched): void {
            $individualDispatched[] = $event;
        });
        $dispatcher->addListener(OktaAccessRequestEvent::class, static function (OktaAccessRequestEvent $event) use (&$groupDispatched): void {
            $groupDispatched[] = $event;
        });

        $payload = (string) json_encode($this->buildGenericPayload('access.request.create'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $individualDispatched);
        self::assertSame('access.request.create', $individualDispatched[0]->oktaEvent->eventType);
        // Group events: individual extends group, so the group listener catches both dispatches
        self::assertGreaterThanOrEqual(1, \count($groupDispatched));
    }

    public function testTypedEventAlsoDispatchesGroupEvent(): void
    {
        $client = static::createClient();
        $groupDispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(OktaUserLifecycleGroupEvent::class, static function (OktaUserLifecycleGroupEvent $event) use (&$groupDispatched): void {
            $groupDispatched[] = $event;
        });

        $payload = (string) json_encode($this->buildLifecyclePayload('user.lifecycle.deactivate'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $groupDispatched);
        self::assertSame('user.lifecycle.deactivate', $groupDispatched[0]->oktaEvent->eventType);
    }

    public function testUnknownEventDispatchesGenericOktaEvent(): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(GenericOktaEvent::class, static function (GenericOktaEvent $event) use (&$dispatched): void {
            $dispatched[] = $event;
        });

        $payload = (string) json_encode($this->buildGenericPayload('completely.unknown.event.type'));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $dispatched);
        self::assertSame('completely.unknown.event.type', $dispatched[0]->oktaEvent->eventType);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGenericPayload(string $eventType): array
    {
        return [
            'data' => [
                'events' => [
                    [
                        'eventType' => $eventType,
                        'target' => [],
                        'actor' => self::ACTOR,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLifecyclePayload(string $eventType): array
    {
        return self::buildLifecyclePayloadStatic($eventType);
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildLifecyclePayloadStatic(string $eventType): array
    {
        return [
            'data' => [
                'events' => [
                    [
                        'eventType' => $eventType,
                        'target' => [
                            [
                                'alternateId' => 'user@example.com',
                                'id' => '00u1234567890',
                                'type' => 'User',
                                'displayName' => 'John Doe',
                            ],
                        ],
                        'actor' => self::ACTOR,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGroupPayload(string $eventType): array
    {
        return self::buildGroupPayloadStatic($eventType);
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildGroupPayloadStatic(string $eventType): array
    {
        return [
            'data' => [
                'events' => [
                    [
                        'eventType' => $eventType,
                        'target' => [
                            [
                                'alternateId' => 'user@example.com',
                                'id' => '00u1234567890',
                                'type' => 'User',
                                'displayName' => 'John Doe',
                            ],
                            [
                                'alternateId' => 'engineering',
                                'id' => '00gGroup123',
                                'type' => 'UserGroup',
                                'displayName' => 'Engineering',
                            ],
                        ],
                        'actor' => self::ACTOR,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAppPayload(string $eventType): array
    {
        return self::buildAppPayloadStatic($eventType);
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildAppPayloadStatic(string $eventType): array
    {
        return [
            'data' => [
                'events' => [
                    [
                        'eventType' => $eventType,
                        'target' => [
                            [
                                'alternateId' => 'user@example.com',
                                'id' => '00u1234567890',
                                'type' => 'User',
                                'displayName' => 'John Doe',
                            ],
                            [
                                'alternateId' => 'Slack',
                                'id' => '0oaApp456',
                                'type' => 'AppInstance',
                                'displayName' => 'Slack',
                            ],
                        ],
                        'actor' => self::ACTOR,
                    ],
                ],
            ],
        ];
    }
}
