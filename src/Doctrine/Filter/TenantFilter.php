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

        // Если мы работаем вне контекста какого-либо арендатора, то возвращать нужно все сущности,
        // фильтр не нужен:
        if (
            !$this->hasParameter('tenant_id')
            || empty(trim($this->getParameter('tenant_id'), '\''))
            || !$this->hasParameter('tenant_field')
        ) {
            return '';
        }

        $targetTenantColumn = $targetEntity->getSingleAssociationJoinColumnName(
            trim($this->getParameter('tenant_field'), '\'')
        );
        $targetTenantId = $this->getParameter('tenant_id');

        // Если наша сущность может принадлежать арендатору, а может не принадлежать (быть общесистемной),
        // то в случае если мы работаем в контексте арендатора, мы возвращаем "его" и "общие" сущности".
        if ($targetEntity->reflClass->implementsInterface(BelongsToTenantOptionalInterface::class)) {
            return sprintf('%s.%s = %s OR %s.%s IS NULL',
                $targetTableAlias,
                $targetTenantColumn,
                $targetTenantId,
                $targetTableAlias,
                $targetTenantColumn,
            );
        }

        // Если же сущность строго принадлежит какому-либо арендатору, то возвращать нужно только "его" сущности.
        return $targetTableAlias.'.'.$targetTenantColumn.' = '.$targetTenantId;
    }
}
