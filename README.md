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
Then install module running this commands:

```
$ composer require local/module-comments
$ bin/magento module:enable Emagento_Comments
$ bin/magento setup:upgrade
```
