<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Ilbee\Okta\Event\Service\EmailHelper;

final class EmailHelperTest extends TestCase
{
    private EmailHelper $emailHelper;

    protected function setUp(): void
    {
        $this->emailHelper = new EmailHelper();
    }

    #[DataProvider('emailProviderForBaseUsername')]
    public function testGetBaseUsername(string $email, string $expectedUsername): void
    {
        self::assertSame($expectedUsername, $this->emailHelper->getBaseUsername($email));
    }

    public static function emailProviderForBaseUsername(): array
    {
        return [
            'simple email' => ['user@example.com', 'user'],
            'email with tag' => ['user+tag@example.com', 'user'],
            'email with multiple tags' => ['user+tag+another@example.com', 'user'],
            'email with dot in username' => ['first.last@example.com', 'first.last'],
            'email with hyphen in username' => ['first-last@example.com', 'first-last'],
            'email with numbers in username' => ['user123@example.com', 'user123'],
            'empty username before @' => ['@example.com', ''],
            'empty username before +' => ['+tag@example.com', ''],
        ];
    }

    #[DataProvider('emailProviderForDomain')]
    public function testGetDomain(string $email, string $expectedDomain): void
    {
        self::assertSame($expectedDomain, $this->emailHelper->getDomain($email));
    }

    public static function emailProviderForDomain(): array
    {
        return [
            'simple email' => ['user@example.com', 'example.com'],
            'email with tag' => ['user+tag@example.com', 'example.com'],
            'email with subdomain' => ['user@sub.example.com', 'sub.example.com'],
            'email with multiple dots in domain' => ['user@sub.domain.co.uk', 'sub.domain.co.uk'],
            'empty domain' => ['user@', ''],
        ];
    }

    #[TestWith(['getBaseUsername'])]
    #[TestWith(['getDomain'])]
    public function testThrowsOnMissingAtSymbol(string $method): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->emailHelper->$method('no-at-symbol');
    }
}
