<?php

namespace SprintF\Bundle\MultiTenant\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use SprintF\Bundle\MultiTenant\Entity\BelongsToTenantInterface;
use SprintF\Bundle\MultiTenant\Entity\BelongsToTenantOptionalInterface;

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
