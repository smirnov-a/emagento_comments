# Magento 2 Local Comments module (Russian)
Данный модуль расширяет стандартный модуль Magento_Reviews и позволяет пользователям оставлять комментарии к магазину. Также он может загружать отзывы к магазину из внешних источников (на данный момент из 2gis и Yandex)
## Table of contents
 * [General info](#general-info)
 * [Technologies](#technologies)
 * [Installation](#installation)
 
## General info
This module 

## Technologies
Module requires Magento 2

## Installation
Add repository into composer.json:
```
"repositories": [
    "name": "local/module-comments",
    "type": "git",
    "url": "https://github.com/smirnov-a/emagento_comments.git"
]
```
Установка производится с помощью команд:
```
$ composer require emagento/module-comments
$ bin/magento module:enable Emagento_Comments
$ bin/magento setup:upgrade
```
В таблице review_entity должна появиться запись с типом *store*:

![local_comments_review_entity](https://user-images.githubusercontent.com/61776819/100909958-2820d300-34ef-11eb-9dfe-cab2ce15255b.png?raw=true "New entity code")


Дальше нужно разрешить в админке модуль и указать настройки внешних комментариев
**Stores -> Configuration -> Local Comments**:

![local_comments_admin_settings2](https://user-images.githubusercontent.com/61776819/100909372-74b7de80-34ee-11eb-99e5-e628ea129fd7.png?raw=true "Backend Local comments configuration")

Вот как выглядит пример работы модуля:

![local_comments_demo_page](https://user-images.githubusercontent.com/61776819/100910507-e80e2000-34ef-11eb-99c5-ccde0a894343.png?raw=true "Example Ui Component")

Здесь Ui-component загружается ajax'ом в левую колонку 2-х колоночной верстки
Внешний вид можно подстроить под себя в собственной теме

Пример работы модуля пожно посмотреть [на странице](https://emagento.ru/demo-comments).
