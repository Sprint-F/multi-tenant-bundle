<?php

namespace SprintF\Bundle\MultiTenant\Context;

use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;

/**
 * Общий интерфейс для объекта контекста,
 * хранящего информацию о текущем арендаторе в рамках текущего цикла "запрос-ответ".
 */
interface TenantContextInterface
{
    /**
     * Получение текущего арендатора.
     */
    public function getTenant(): ?TenantInterface;

    /**
     * Смена текущего арендатора на нового.
     */
    public function setTenant(TenantInterface $tenant): static;

    /**
     * Работаем ли мы в данный момент в контексте какого-либо арендатора?
     */
    public function hasTenant(): bool;

    /**
     * Переход в режим "Без арендатора".
     */
    public function clearTenant(): static;
}
