# multi-tenant-bundle
Symfony Multi-Tenant Bundle

# Для пользователей
Чтобы корректно отработали рецепты Flex, сначала добавьте в composer.json:
```json
    "extra": {
        "symfony": {
            "endpoint": [
                "https://api.github.com/repos/Sprint-F/multi-tenant-bundle-flex/contents/index.json",
                "flex://defaults"
            ]
        }
    }
```

Затем устанавливайте бандл
```shell
composer require "sprintf/multi-tenant-bundle"
```

# Для разработчиков

## Code style fix
```shell
php vendor/bin/php-cs-fixer fix
```

## Run Tests
```shell
php vendor/bin/codecept run Unit
```
