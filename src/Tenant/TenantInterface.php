<?php

namespace SprintF\Bundle\MultiTenant\Tenant;

/**
 * Общий интерфейс для сущностей арендаторов.
 */
interface TenantInterface
{
    /**
     * ID арендатора.
     * В редких случаях, к примеру: до сохранения в базу данных, может быть null.
     */
    public function getId(): int|string|\Stringable|null;

    /**
     * Символическое имя арендатора.
     */
    public function getSlug(): string;
}
