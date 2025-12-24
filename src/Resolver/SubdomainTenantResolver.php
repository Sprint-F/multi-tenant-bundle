<?php

namespace SprintF\Bundle\MultiTenant\Resolver;

use SprintF\Bundle\MultiTenant\Registry\TenantRegistryInterface;
use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Резолвер арендатора на основе субдомена хоста запроса.
 */
class SubdomainTenantResolver implements TenantResolverInterface
{
    public function __construct(
        private readonly TenantRegistryInterface $registry,
        private readonly string $baseDomain,
        private readonly array $excludedSubdomains,
    ) {
    }

    public function resolveTenant(Request $request): ?TenantInterface
    {
        $host = $request->getHost();
        if (!str_ends_with($host, $this->baseDomain)) {
            return null;
        }

        $subdomain = str_replace('.'.$this->baseDomain, '', $host);
        // Не работаем с хостом без субдомена, с субдоменами из списка исключенных и с многоуровневыми доменами
        if ($subdomain === $this->baseDomain || in_array($subdomain, $this->excludedSubdomains, true) || str_contains($subdomain, '.')) {
            return null;
        }

        return $this->registry->findOneBySlug($subdomain);
    }
}
