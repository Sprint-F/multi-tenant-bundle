<?php

namespace SprintF\Bundle\MultiTenant\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('multi_tenant');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('tenant_entity')
                    ->cannotBeEmpty()
                    ->defaultValue('\App\Entity\Tenant')
                    ->info('The fully qualified class name of tenant entity')
                ->end() // tenant_entity
            ->end() // children
        ;

        return $treeBuilder;
    }
}
