<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Controller;

use Ilbee\Okta\Event\DTO\OktaWebhookPayload;
use Ilbee\Okta\Event\Event\GenericOktaEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserLifecycleEvent;
use Ilbee\Okta\Event\Service\OktaEventIdStoreInterface;
use Ilbee\Okta\Event\Service\OktaEventMapper;
use Ilbee\Okta\Event\Service\OktaEventRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class OktaWebhookController
{
    private const BACKWARD_COMPAT_EVENT_TYPES = [
        OktaUserLifecycleEvent::DEACTIVATE,
        OktaUserLifecycleEvent::SUSPEND,
    ];

    private const MAX_LOG_FIELD_LENGTH = 255;

    public function __construct(
        private SerializerInterface $serializer,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
        private string $webhookSecret,
        private OktaEventMapper $eventMapper,
        private OktaEventIdStoreInterface $eventIdStore,
        private bool $verificationEnabled,
        private int $maxPayloadSize,
        private int $maxEventsPerRequest,
        private OktaEventRegistry $eventRegistry = new OktaEventRegistry(),
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $authToken = $request->headers->get('X-Auth-Token');
        if (!$authToken) {
            $this->logger->error('X-Auth-Token header is missing.');

            return new Response('X-Auth-Token header is missing.', Response::HTTP_BAD_REQUEST);
        }

        if (!hash_equals($this->webhookSecret, $authToken)) {
            $this->logger->error('Invalid X-Auth-Token.');

            return new Response('Invalid token.', Response::HTTP_FORBIDDEN);
        }

        if ($request->isMethod('GET')) {
            return $this->handleVerification($request);
        }

        $contentType = $request->headers->get('Content-Type', '');
        if (!str_starts_with($contentType, 'application/json')) {
            $this->logger->warning('Unsupported Content-Type.', ['content_type' => $contentType]);

            return new Response('Unsupported Media Type.', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        $contentLength = $request->headers->get('Content-Length');
        if (null !== $contentLength && (int) $contentLength > $this->maxPayloadSize) {
            $this->logger->warning('Content-Length exceeds maximum allowed size.', [
                'content_length' => (int) $contentLength,
                'max' => $this->maxPayloadSize,
            ]);

            return new Response('Payload too large.', Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        $body = $request->getContent();
        if (\strlen($body) > $this->maxPayloadSize) {
            $this->logger->warning('Payload exceeds maximum allowed size.', [
                'size' => \strlen($body),
                'max' => $this->maxPayloadSize,
            ]);

            return new Response('Payload too large.', Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        try {
            /** @var OktaWebhookPayload $payload */
            $payload = $this->serializer->deserialize(
                $body,
                OktaWebhookPayload::class,
                'json'
            );
        } catch (NotEncodableValueException $e) {
            $this->logger->warning('Invalid JSON payload received.', ['exception' => $e]);

            return new Response('Invalid JSON payload.', Response::HTTP_BAD_REQUEST);
        } catch (PartialDenormalizationException $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = \sprintf('Property "%s": %s', $error->getPath(), $error->getMessage());
            }
            $this->logger->warning('Partial denormalization error.', ['errors' => $errors]);

            return new Response('Invalid payload.', Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error processing Okta webhook.', ['exception' => $e]);

            return new Response('Internal server error.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $events = $payload->data->events;
        $eventCount = \count($events);

        if ($eventCount > $this->maxEventsPerRequest) {
            $this->logger->warning('Payload contains more events than allowed, truncating.', [
                'total' => $eventCount,
                'max' => $this->maxEventsPerRequest,
            ]);
            $events = \array_slice($events, 0, $this->maxEventsPerRequest);
        }

        foreach ($events as $oktaEvent) {
            if (null !== $oktaEvent->uuid && $this->eventIdStore->has($oktaEvent->uuid)) {
                $this->logger->info('Skipping duplicate event.', [
                    'uuid' => $this->truncateLogField($oktaEvent->uuid),
                ]);

                continue;
            }

            if (null !== $oktaEvent->uuid) {
                $this->eventIdStore->add($oktaEvent->uuid);
            }

            // 1. Dispatch typed event (user lifecycle, group membership, app membership)
            if ($this->eventMapper->supports($oktaEvent->eventType)) {
                try {
                    $typedEvent = $this->eventMapper->map($oktaEvent);
                } catch (\Throwable $e) {
                    $this->logger->warning('Failed to map Okta event.', [
                        'eventType' => $this->truncateLogField($oktaEvent->eventType),
                        'exception' => $e,
                    ]);
                    $typedEvent = null;
                }

                if (null !== $typedEvent) {
                    $this->eventDispatcher->dispatch($typedEvent);

                    if (\in_array($oktaEvent->eventType, self::BACKWARD_COMPAT_EVENT_TYPES, true)) {
                        $this->eventDispatcher->dispatch(new OktaUserLifecycleEvent($typedEvent->userEmail, $typedEvent->eventType));
                    }
                }
            }

            // 2. Dispatch individual event (e.g. OktaAccessRequestCreatedEvent)
            $individualEvent = $this->eventRegistry->createIndividualEvent($oktaEvent);
            if (null !== $individualEvent) {
                $this->eventDispatcher->dispatch($individualEvent);
            }

            // 3. Dispatch group event (e.g. OktaAccessRequestEvent)
            $groupEvent = $this->eventRegistry->createGroupEvent($oktaEvent);
            if (null !== $groupEvent) {
                $this->eventDispatcher->dispatch($groupEvent);
            }

            // 4. Fallback: dispatch GenericOktaEvent for completely unknown events
            if (null === $individualEvent && null === $groupEvent && !$this->eventMapper->supports($oktaEvent->eventType)) {
                $this->logger->info('Dispatching generic event for unknown Okta event type.', [
                    'eventType' => $this->truncateLogField($oktaEvent->eventType),
                ]);
                $this->eventDispatcher->dispatch(new GenericOktaEvent($oktaEvent));
            }
        }

        return new Response('Webhook processed.', Response::HTTP_OK);
    }

    private function handleVerification(Request $request): Response
    {
        if (!$this->verificationEnabled) {
            return new Response('Verification endpoint is disabled.', Response::HTTP_NOT_FOUND);
        }

        $challenge = $request->headers->get('x-okta-verification-challenge');

        if (!$challenge || !preg_match('/^[a-zA-Z0-9\-_]{8,256}$/', $challenge)) {
            $this->logger->error('Invalid or missing X-Okta-Verification-Challenge header.');

            return new Response('Invalid or missing verification challenge.', Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['verification' => $challenge]);
    }

    private function truncateLogField(string $value): string
    {
        if (mb_strlen($value) > self::MAX_LOG_FIELD_LENGTH) {
            return mb_substr($value, 0, self::MAX_LOG_FIELD_LENGTH).'...';
        }

        return $value;
    }
}
