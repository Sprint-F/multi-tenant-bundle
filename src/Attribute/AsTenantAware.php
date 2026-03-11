<?php

namespace SprintF\Bundle\MultiTenant\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsTenantAware
{
    public function __construct(
        // Уровень изоляции данной сущности в общем контексте
        public CommonContextIsolation $commonContextIsolation = CommonContextIsolation::COMMON,
        // Уровень изоляции данной сущности в частном контексте
        public PrivateContextIsolation $privateContextIsolation = PrivateContextIsolation::TENANT,
    ) {
    }
}
