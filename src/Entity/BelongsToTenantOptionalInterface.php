<?php

namespace SprintF\Bundle\MultiTenant\Entity;

use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;

/**
 * Общий интерфейс для сущностей, которые могут принадлежать какому-либо арендатору (а могут - и не принадлежать).
 */
interface BelongsToTenantOptionalInterface
{
    public function getTenant(): ?TenantInterface;

    public function setTenant(?TenantInterface $tenant): static;
}
