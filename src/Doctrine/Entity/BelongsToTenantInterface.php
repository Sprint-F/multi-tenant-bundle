<?php

namespace SprintF\Bundle\MultiTenant\Doctrine\Entity;

use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;

/**
 * Общий интерфейс для сущностей, которые должны однозначно принадлежать какому-либо арендатору.
 */
interface BelongsToTenantInterface
{
    public function getTenant(): TenantInterface;

    public function setTenant(TenantInterface $tenant): static;
}
