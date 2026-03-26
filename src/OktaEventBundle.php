<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class OktaEventBundle extends AbstractBundle
{
    /**
     * Defines the bundle's configuration tree.
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $definition->rootNode();

        $rootNode
            ->children()
                ->scalarNode('route')
                    ->defaultValue('/okta/webhook')
                    ->info('The path for the okta webhook endpoint.')
                ->end()
                ->scalarNode('webhook_secret')
                    ->info('The secret key used to verify the signature of incoming Okta webhooks.')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->booleanNode('verification_enabled')
                    ->defaultTrue()
                    ->info('Whether the GET verification endpoint is enabled. Disable after initial Okta verification.')
                ->end()
                ->integerNode('max_payload_size')
                    ->defaultValue(1_048_576)
                    ->min(1024)
                    ->info('Maximum allowed payload size in bytes (default: 1 MB).')
                ->end()
                ->integerNode('max_events_per_request')
                    ->defaultValue(100)
                    ->min(1)
                    ->info('Maximum number of events processed per webhook request.')
                ->end()
            ->end()
        ;
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('.', 'oktaevent_route_loader');
    }

    /**
     * Loads the bundle's configuration and services.
     *
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__.'/Resources/config/services.yaml');

        $container->parameters()
            ->set('okta_event.route', $config['route'])
            ->set('okta_event.webhook_secret', $config['webhook_secret'])
            ->set('okta_event.verification_enabled', $config['verification_enabled'])
            ->set('okta_event.max_payload_size', $config['max_payload_size'])
            ->set('okta_event.max_events_per_request', $config['max_events_per_request']);
    }
}
