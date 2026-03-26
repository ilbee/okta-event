<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests;

use PHPUnit\Framework\TestCase;
use Ilbee\Okta\Event\OktaEventBundle;

class OktaEventBundleTest extends TestCase
{
    public function testInstantiateOktaEventBundle(): void
    {
        self::assertInstanceOf(OktaEventBundle::class, new OktaEventBundle());
    }
}
