<?php

namespace SprintF\Bundle\MultiTenant\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use SprintF\Bundle\MultiTenant\Doctrine\Entity\BelongsToTenantInterface;
use SprintF\Bundle\MultiTenant\Doctrine\Entity\BelongsToTenantOptionalInterface;

/**
 * Фильтр для Doctrine.
 * Добавляет к запросам условие вида "entity.tenant_id=:id", где :id - идентификатор текущего арендатора.
 */
class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (
            !$targetEntity->reflClass?->implementsInterface(BelongsToTenantInterface::class)
            && !$targetEntity->reflClass?->implementsInterface(BelongsToTenantOptionalInterface::class)
        ) {
            return '';
        }

        if (!$this->hasParameter('tenant_id') || empty(trim($this->getParameter('tenant_id'), '\''))) {
            return '';
        }

        return $targetTableAlias.'tenant = '.$this->getParameter('tenant_id');
    }
}
