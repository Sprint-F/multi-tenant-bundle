<?php

namespace SprintF\Bundle\MultiTenant\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use SprintF\Bundle\MultiTenant\Attribute\AsTenantAware;
use SprintF\Bundle\MultiTenant\Attribute\CommonContextIsolation;
use SprintF\Bundle\MultiTenant\Attribute\PrivateContextIsolation;
use SprintF\Bundle\MultiTenant\Doctrine\Entity\BelongsToTenantInterface;
use SprintF\Bundle\MultiTenant\Doctrine\Entity\BelongsToTenantOptionalInterface;

/**
 * Фильтр для Doctrine.
 * Добавляет к запросам условия вида "entity.tenant_id=:id", где :id - идентификатор текущего арендатора.
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

        // Определяем уровни изоляции данной сущности. Берем информацию из атрибута #[AsTenantAware]
        $tenantAwareAttributes = $targetEntity->reflClass?->getAttributes(AsTenantAware::class);
        if (!empty($tenantAwareAttributes)) {
            /** @var AsTenantAware $attribute */
            $attribute = $tenantAwareAttributes[0]->newInstance();
            /** @var AsTenantAware $isolation */
            $commonIsolation = $attribute->commonContextIsolation;
            $privateIsolation = $attribute->privateContextIsolation;
        } else {
            $commonIsolation = CommonContextIsolation::COMMON;
            $privateIsolation = PrivateContextIsolation::TENANT;
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

        // Далее, в зависимости от контекста аренды и уровня изоляции, строим запрос фильтра:
        if (null === $tenant) {
            // Если мы в общем контексте и...
            switch ($commonIsolation) {
                case CommonContextIsolation::COMMON:
                    // Сущность изолирована (доступны только общие сущности)
                    return $targetTableAlias.'.'.$targetTenantColumn.' IS NULL';
                case CommonContextIsolation::ALL:
                    // Сущность не изолирована (доступны все)
                    return '';
            }
        } else {
            // Если мы в частном контексте и...
            switch ($privateIsolation) {
                case PrivateContextIsolation::TENANT:
                    // Сущность изолирована (доступны только свои)
                    return $targetTableAlias.'.'.$targetTenantColumn.' = '.$targetTenantId;
                case PrivateContextIsolation::COMMON:
                    // Сущность частично изолирована (доступны и свои и общие)
                    return sprintf('%s.%s = %s OR %s.%s IS NULL',
                        $targetTableAlias,
                        $targetTenantColumn,
                        $targetTenantId,
                        $targetTableAlias,
                        $targetTenantColumn,
                    );
            }
        }

        return '';
    }
}
