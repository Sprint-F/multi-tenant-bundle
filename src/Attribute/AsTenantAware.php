<?php

namespace SprintF\Bundle\MultiTenant\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsTenantAware
{
    public function __construct(
        // Уровень изоляции данной сущности
        public TenantAwareIsolation $isolation = TenantAwareIsolation::FULL,
    ) {
    }
}
