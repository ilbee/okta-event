<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class WebhookSecretEnvCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('okta_event.webhook_secret')) {
            return;
        }

        $value = $container->getParameter('okta_event.webhook_secret');

        if (\is_string($value) && !str_starts_with($value, '%env(')) {
            trigger_error(
                'okta_event.webhook_secret contains a plaintext secret. '
                .'Use an environment variable instead: webhook_secret: \'%env(OKTA_WEBHOOK_SECRET)%\'',
                \E_USER_WARNING,
            );
        }
    }
}
