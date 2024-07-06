[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

# Magento 2 Local Comments module (Russian)

## Содержание
 * [Общая информация](#general-info)
 * [Установка](#installation)
 * [Удаление](#unisntall)
 
## <a name="general-info"></a>Общая информация
Данный модуль расширяет стандартный модуль Magento_Reviews и позволяет пользователям оставлять комментарии к магазину. Также он может загружать отзывы к магазину из внешних источников 
(на данный момент из Flamp и Yandex) 

## <a name="installation"></a>Установка

Установить можно с помощью команд:
```
$ composer require emagento/module-comments
$ bin/magento module:enable Emagento_Comments
$ bin/magento setup:upgrade
```
В таблице review_entity должна появиться запись с типом *store*:

![local_comments_review_entity](https://user-images.githubusercontent.com/61776819/100909958-2820d300-34ef-11eb-9dfe-cab2ce15255b.png?raw=true "New entity code")


Дальше нужно разрешить в backend'е модуль и указать настройки внешних истоников
**Stores -> Configuration -> Local Comments**:

![local_comments_admin_settings2](https://user-images.githubusercontent.com/61776819/100909372-74b7de80-34ee-11eb-99e5-e628ea129fd7.png?raw=true "Backend Local comments configuration")

Затем нужно перейти в настройки рейтинга: **Stores -> Rating**, выбрать рейтинг с типом *Store*, указать видимость на складах и сохранить:
![local_comments_rating_setup](https://user-images.githubusercontent.com/61776819/101064319-ad6ebb00-35b5-11eb-8993-e950eb7085af.png?raw=true "Rating setup")

Комментарии к магазину можно посмотреть по пути: **Marketing -> Store Reviews**
![local_comments_store_comments2](https://user-images.githubusercontent.com/61776819/101064993-6c2adb00-35b6-11eb-8103-ef86185ee95d.png?raw=true "Store Reviews")

Список выглядит так:
![local_comments_grid](https://user-images.githubusercontent.com/61776819/101272991-1c653300-37b3-11eb-9c80-5b3b127815bd.png?raw=true "Store Reviews grid")

А так выглядит пример работы модуля на фронтенде:

![local_comments_demo_page](https://user-images.githubusercontent.com/61776819/100910507-e80e2000-34ef-11eb-99c5-ccde0a894343.png?raw=true "Example Ui Component")

Здесь Ui-component загружается ajax'ом в левую колонку 2-х колоночной верстки
Внешний вид можно подстроить под себя в собственной теме

Пример работы модуля пожно посмотреть [на странице](https://emagento.ru/demo-comments).

## <a name="uninstall"></a>Удаление
```
php bin/magento module:uninstall -r Emagento_Comments
php bin/magento setup:upgrade
```

## License

GPL-3.0-or-later
