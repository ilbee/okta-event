<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Fixtures;

use Ilbee\Okta\Event\OktaEventBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Test kernel with restrictive security settings for testing limits.
 */
class RestrictedKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new OktaEventBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'test' => true,
            'secret' => 'test',
            'serializer' => ['enabled' => true],
            'property_access' => ['enabled' => true],
            'property_info' => ['enabled' => true],
        ]);

        $container->loadFromExtension('okta_event', [
            'webhook_secret' => 'test_secret',
            'verification_enabled' => false,
            'max_payload_size' => 1024,
            'max_events_per_request' => 2,
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        foreach ($this->registerBundles() as $bundle) {
            if ($bundle instanceof OktaEventBundle) {
                $bundle->configureRoutes($routes);
            }
        }
    }
}
