<?php

namespace SprintF\Bundle\MultiTenant\Context;

use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;

/**
 * Обший интерфейс для объекта контекста,
 * хранящего информацию о текущем арендаторе в рамках текущего цикла "запрос-ответ".
 */
interface TenantContextInterface
{
    public function getTenant(): ?TenantInterface;

    public function setTenant(TenantInterface $tenant): static;

    public function hasTenant(): bool;

    public function clearTenant(): static;
}
