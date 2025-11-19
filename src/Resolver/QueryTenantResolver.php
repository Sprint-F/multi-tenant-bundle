<?php

namespace SprintF\Bundle\MultiTenant\Resolver;

use SprintF\Bundle\MultiTenant\Registry\TenantRegistryInterface;
use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Резолвер арендатора на основе данных из get-параметров запроса.
 * Рекомендуется к использованию в тестовых целях.
 */
class QueryTenantResolver implements TenantResolverInterface
{
    public function __construct(
        private readonly TenantRegistryInterface $registry,
        private readonly string $parameterName,
    ) {
    }

    public function resolveTenant(Request $request): ?TenantInterface
    {
        $slug = $request->query->get($this->parameterName);

        if (empty($slug) || !is_string($slug)) {
            return null;
        }

        return $this->registry->findOneBySlug($slug);
    }
}
