<?php

namespace SprintF\Bundle\MultiTenant\DependencyInjection\Compiler;

use SprintF\Bundle\MultiTenant\Doctrine\TenantFilter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineFilterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.orm.filters')) {
            $container->setParameter('doctrine.orm.filters', []);
        }

        $filters = $container->getParameter('doctrine.orm.filters');
        $filters['tenant_filter'] = [
            'class' => TenantFilter::class,
            'enabled' => false,
        ];

        $container->setParameter('doctrine.orm.filters', $filters);
    }
}
