<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Controller;

use Ilbee\Okta\Event\Tests\Fixtures\RestrictedKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for security hardening features (payload limits, verification toggle, events cap).
 */
class OktaWebhookSecurityTest extends WebTestCase
{
    private const ACTOR = [
        'id' => '00uadmin123',
        'type' => 'User',
        'alternateId' => 'admin@example.com',
        'displayName' => 'Admin User',
    ];

    protected static function getKernelClass(): string
    {
        return RestrictedKernel::class;
    }

    public function testGetVerificationReturnsNotFoundWhenDisabled(): void
    {
        $client = static::createClient();
        $client->request('GET', '/okta/webhook', [], [], [
            'HTTP_X-Auth-Token' => 'test_secret',
            'HTTP_x-okta-verification-challenge' => 'some-valid-challenge',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPostWithWrongContentTypeReturns415(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret', 'CONTENT_TYPE' => 'text/plain'],
            '{"data":{"events":[]}}'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    public function testPostWithMissingContentTypeReturns415(): void
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

        $this->assertResponseStatusCodeSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    public function testContentLengthExceedingMaxIsRejectedBeforeReadingBody(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            [
                'HTTP_X-Auth-Token' => 'test_secret',
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Content-Length' => '9999',
            ],
            '{"data":{"events":[]}}'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    public function testPayloadExceedingMaxSizeIsRejected(): void
    {
        $client = static::createClient();

        // RestrictedKernel sets max_payload_size to 1024 bytes
        $oversizedPayload = str_repeat('x', 2048);

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret', 'CONTENT_TYPE' => 'application/json'],
            $oversizedPayload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    public function testPayloadWithinMaxSizeIsAccepted(): void
    {
        $client = static::createClient();

        $payload = (string) json_encode([
            'data' => [
                'events' => [
                    [
                        'eventType' => 'user.lifecycle.deactivate',
                        'target' => [
                            [
                                'alternateId' => 'user@example.com',
                                'id' => '00u123',
                                'type' => 'User',
                                'displayName' => 'John',
                            ],
                        ],
                        'actor' => self::ACTOR,
                    ],
                ],
            ],
        ]);

        self::assertLessThanOrEqual(1024, \strlen($payload));

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret', 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseIsSuccessful();
    }

    public function testEventsAreCappedAtMaxPerRequest(): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(
            \Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent::class,
            static function ($event) use (&$dispatched): void {
                $dispatched[] = $event;
            }
        );

        // RestrictedKernel sets max_events_per_request to 2, send 4 events
        $events = [];
        for ($i = 0; $i < 4; ++$i) {
            $events[] = [
                'eventType' => 'user.lifecycle.deactivate',
                'target' => [
                    [
                        'alternateId' => "user{$i}@example.com",
                        'id' => "00u{$i}",
                        'type' => 'User',
                        'displayName' => "User {$i}",
                    ],
                ],
                'actor' => self::ACTOR,
            ];
        }

        $payload = (string) json_encode(['data' => ['events' => $events]]);

        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret', 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        // Only 2 events should have been dispatched (max_events_per_request = 2)
        self::assertCount(2, $dispatched);
    }

    public function testMalformedPayloadReturnsGenericError(): void
    {
        $client = static::createClient();

        // Send invalid JSON to trigger a bad request response
        $client->request(
            'POST',
            '/okta/webhook',
            [],
            [],
            ['HTTP_X-Auth-Token' => 'test_secret', 'CONTENT_TYPE' => 'application/json'],
            '{not valid json'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = (string) $client->getResponse()->getContent();
        // Should NOT contain stack traces or field-level details
        self::assertStringNotContainsString('Property', $content);
        self::assertStringNotContainsString('Exception', $content);
    }

    public function testEventWithUuidFieldIsDeserialized(): void
    {
        $client = static::createClient();
        $dispatched = [];

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(
            \Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent::class,
            static function ($event) use (&$dispatched): void {
                $dispatched[] = $event;
            }
        );

        $payload = (string) json_encode([
            'data' => [
                'events' => [
                    [
                        'uuid' => 'abc-123-def-456',
                        'published' => '2026-03-18T10:00:00.000Z',
                        'eventType' => 'user.lifecycle.deactivate',
                        'target' => [
                            [
                                'alternateId' => 'user@example.com',
                                'id' => '00u123',
                                'type' => 'User',
                                'displayName' => 'John',
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
            ['HTTP_X-Auth-Token' => 'test_secret', 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $dispatched);
    }
}
