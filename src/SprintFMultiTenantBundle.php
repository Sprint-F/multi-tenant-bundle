<?php

namespace SprintF\Bundle\MultiTenant;

use SprintF\Bundle\MultiTenant\Registry\DoctrineRepositoryTenantRegistry;
use SprintF\Bundle\MultiTenant\Registry\TenantRegistryInterface;
use SprintF\Bundle\MultiTenant\Resolver\QueryTenantResolver;
use SprintF\Bundle\MultiTenant\Resolver\TenantResolverInterface;
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

        // Регистрируем реестр арендаторов, указываем ему имя класса сущности арендатора.
        $builder->register(DoctrineRepositoryTenantRegistry::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$tenantEntityClass', $config['tenant_entity']);
        $builder->setAlias(TenantRegistryInterface::class, DoctrineRepositoryTenantRegistry::class);

        // Регистрируем конкретный резолвер арендаторов, выбирая на основе конфигурации бандла:
        switch ($config['resolver']) {
            // Резолвер на базе данных из get-параметров запроса
            case 'query':
                $builder->register(QueryTenantResolver::class)
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setArgument('$parameterName', $config['query']['parameter']);
                $builder->setAlias(TenantResolverInterface::class, QueryTenantResolver::class);
                break;
        }
    }
}
