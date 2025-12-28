<?php

namespace SprintF\Bundle\MultiTenant\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use SprintF\Bundle\MultiTenant\Attribute\AsTenantAware;
use SprintF\Bundle\MultiTenant\Attribute\TenantAwareIsolation;
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
        // Мы работаем только с сущностями, в которых явно указана мультиарендность:
        if (
            !$targetEntity->reflClass?->implementsInterface(BelongsToTenantInterface::class)
            && !$targetEntity->reflClass?->implementsInterface(BelongsToTenantOptionalInterface::class)
        ) {
            return '';
        }

        // Определяем уровень изоляции данной сущности. Берем информацию из атрибута #[AsTenantAware]
        $tenantAwareAttributes = $targetEntity->reflClass?->getAttributes(AsTenantAware::class);
        if (!empty($tenantAwareAttributes)) {
            $attribute = $tenantAwareAttributes[0]->newInstance();
            /** @var AsTenantAware $isolation */
            $isolation = $attribute->isolation;
        } else {
            $isolation = TenantAwareIsolation::FULL;
        }

        // Определим контекст аренды:
        if (
            !$this->hasParameter('tenant_id')
            || empty($tenant = trim($this->getParameter('tenant_id'), '\''))
        ) {
            $tenant = null;
        }

        // Имя поля связи данной сущности с арендатором
        $targetTenantColumn = $targetEntity->getSingleAssociationJoinColumnName(
            $this->hasParameter('tenant_field') ? trim($this->getParameter('tenant_field'), '\'') : 'tenant'
        );

        // ID арендатора, подготовленный к вставке в запрос
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
