<?php

namespace SprintF\Bundle\MultiTenant\Resolver;

use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Общий интерфейс для резолверов: классов, которые на основании запроса клиента могут определить арендатора.
 */
interface TenantResolverInterface
{
    /**
     * Метод, который пытается по запросу от клиента определить арендатора.
     * Возвращается либо объект арендатора, либо null, если определение не удалось.
     */
    public function resolveTenant(Request $request): ?TenantInterface;
}
