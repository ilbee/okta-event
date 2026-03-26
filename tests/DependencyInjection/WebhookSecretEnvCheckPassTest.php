<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\DependencyInjection;

use Ilbee\Okta\Event\DependencyInjection\WebhookSecretEnvCheckPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WebhookSecretEnvCheckPassTest extends TestCase
{
    public function testWarnsOnPlaintextSecret(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('okta_event.webhook_secret', 'my_plaintext_secret');

        $pass = new WebhookSecretEnvCheckPass();

        $warning = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$warning): bool {
            $warning = $errstr;

            return true;
        }, \E_USER_WARNING);

        try {
            $pass->process($container);
        } finally {
            restore_error_handler();
        }

        self::assertNotNull($warning);
        self::assertStringContainsString('okta_event.webhook_secret contains a plaintext secret', $warning);
    }

    public function testNoWarningWithEnvVariable(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('okta_event.webhook_secret', '%env(OKTA_WEBHOOK_SECRET)%');

        $pass = new WebhookSecretEnvCheckPass();

        $warned = false;
        set_error_handler(static function () use (&$warned): bool {
            $warned = true;

            return true;
        }, \E_USER_WARNING);

        try {
            $pass->process($container);
        } finally {
            restore_error_handler();
        }

        self::assertFalse($warned);
    }

    public function testNoWarningWhenParameterMissing(): void
    {
        $container = new ContainerBuilder();

        $pass = new WebhookSecretEnvCheckPass();

        $pass->process($container);

        $this->addToAssertionCount(1);
    }
}
