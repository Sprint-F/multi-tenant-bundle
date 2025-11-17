<?php

namespace SprintF\Bundle\MultiTenant\Context;

use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Стандартная реализация интерфейса TenantContextInterface:
 * текущий контекст арендатора.
 */
#[AsAlias]
class TenantContext implements TenantContextInterface
{
    private ?TenantInterface $tenant = null;

    public function getTenant(): ?TenantInterface
    {
        return $this->tenant;
    }

    public function setTenant(TenantInterface $tenant): static
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function hasTenant(): bool
    {
        return null !== $this->tenant;
    }

    public function clearTenant(): static
    {
        $this->tenant = null;

        return $this;
    }
}
