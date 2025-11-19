<?php

namespace SprintF\Bundle\MultiTenant;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SprintFMultiTenantBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()

                // Конфигурация сущности арендатора
                ->scalarNode('tenant_entity')
                    ->cannotBeEmpty()
                    ->defaultValue('\\App\\Entity\\Tenant')
                    ->info('The fully qualified class name of tenant entity')
                ->end() // tenant_entity

                // Конфигурация резолвера арендатора
                ->enumNode('resolver')
                    ->cannotBeEmpty()
                    ->values(['query'])
                    ->defaultValue('query')
                    ->info('The name of the tenant resolver')
                ->end()

                // Конфигурация резолвера на основе данных из get-параметров запроса
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('parameter')
                            ->defaultValue('tenant')
                            ->info('Query parameter name to use for tenant resolution')
                        ->end()
                    ->end()
                ->end()

            ->end() // children
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
    }
}
