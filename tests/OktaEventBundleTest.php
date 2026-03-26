<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests;

use PHPUnit\Framework\TestCase;
use Ilbee\Okta\Event\OktaEventBundle;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class OktaEventBundleTest extends TestCase
{
    public function testInstantiateOktaEventBundle(): void
    {
        $bundle = new OktaEventBundle();
        /** @var array<string, class-string> $parents */
        $parents = class_parents($bundle);
        self::assertArrayHasKey(AbstractBundle::class, $parents);
    }
}
