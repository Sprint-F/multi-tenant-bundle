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

        switch ($isolation) {
            // Если установлен ПОЛНЫЙ уровень изоляции, то...
            case TenantAwareIsolation::FULL:
                if (null === $tenant) {
                    // ... в общем контексте доступны только общие сущности
                    return $targetTableAlias.'.'.$targetTenantColumn.' IS NULL';
                } else {
                    // ... в контексте арендатора - только его сущности
                    return $targetTableAlias.'.'.$targetTenantColumn.' = '.$targetTenantId;
                }
                break;

                // Если установлен ПРИВАТНЫЙ уровень изоляции, то...
            case TenantAwareIsolation::PRIVATE:
                if (null === $tenant) {
                    // ... в общем контексте доступны все сущности
                    return '';
                } else {
                    // ... в контексте арендатора - только его сущности
                    return $targetTableAlias.'.'.$targetTenantColumn.' = '.$targetTenantId;
                }
                break;

                // Если установлен ОБЩИЙ уровень изоляции, то...
            case TenantAwareIsolation::COMMON:
                if (null === $tenant) {
                    // ... в общем контексте доступны только общие сущности
                    return $targetTableAlias.'.'.$targetTenantColumn.' IS NULL';
                } else {
                    // ... в контексте арендатора - его сущности и общие сущности
                    return sprintf('%s.%s = %s OR %s.%s IS NULL',
                        $targetTableAlias,
                        $targetTenantColumn,
                        $targetTenantId,
                        $targetTableAlias,
                        $targetTenantColumn,
                    );
                }
                break;

                // Если установлен ГЕНЕРАЛЬНЫЙ уровень изоляции, то...
            case TenantAwareIsolation::GENERAL:
                if (null === $tenant) {
                    // ... в общем контексте доступны все сущности
                    return '';
                } else {
                    // ... в контексте арендатора - его сущности и общие сущности
                    return sprintf('%s.%s = %s OR %s.%s IS NULL',
                        $targetTableAlias,
                        $targetTenantColumn,
                        $targetTenantId,
                        $targetTableAlias,
                        $targetTenantColumn,
                    );
                }
                break;
        }

        return '';
    }
}
