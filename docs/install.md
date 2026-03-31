# Установка бандла

## Flex
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

## Установка
Затем устанавливайте бандл
```shell
composer require "sprintf/multi-tenant-bundle"
```

