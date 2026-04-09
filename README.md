# Cart Plugin + — WordPress Plugin

**WordPress PHP Version License**

**Cart Plugin +** — это профессиональный плагин корзины для WordPress, разработанный специально для работы с кастомными типами записей (CPT) и полями ACF. Плагин обеспечивает полную функциональность корзины товаров с оформлением заявок, генерацией PDF-документов и отправкой уведомлений администратору.

🔗 **Связанная тема:** [Tattoo Theme на GitHub](https://github.com/kenny8/site)

---

## 📋 Содержание

- [О плагине](#-о-плагине)
- [Возможности](#-возможности)
- [Структура плагина](#-структура-плагина)
- [Требования](#-требования)
- [Установка](#-установка)
- [Настройка плагина](#-настройка-плагина)
- [Пользовательские типы записей](#-пользовательские-типы-записей)
- [Таксономии](#-таксономии)
- [AJAX API](#-ajax-api)
- [Генерация PDF](#-генерация-pdf)
- [Dashboard виджеты](#-dashboard-виджеты)
- [Разработка и расширение](#-разработка-и-расширение)
- [Changelog](#-changelog)

---

## 🎨 О плагине

**Cart Plugin +** — современное решение для организации корзины товаров на базе кастомных типов записей WordPress. Плагин идеально интегрируется с темой **Tattoo Theme** и поддерживает мультибизнес-проекты, включающие:

- 🪒 **Барбершоп** — продажа продукции, оформление предзаказов
- 🎨 **Тату-салон** — заказ оборудования и материалов
- 🎓 **Академия** — запись на курсы, консультации
- 🧼 **Химчистка** — оформление заказов на услуги

Плагин использует **localStorage** для хранения корзины на стороне клиента, **WordPress AJAX API** для динамической обработки данных и **DomPDF** для генерации PDF-документов заказов.

---

## ✨ Возможности

### 🎯 Функциональность

| Функция | Описание |
|---------|----------|
| ✅ **Корзина на localStorage** | Хранение товаров в браузере пользователя без базы данных |
| ✅ **ACF Integration** | Гибкий маппинг полей ACF для карточек товаров |
| ✅ **Таксономии** | Поддержка брендов и категорий товаров |
| ✅ **Модальное окно корзины** | Красивое всплывающее окно с анимацией |
| ✅ **Валидация форм** | Проверка имени и телефона перед отправкой |
| ✅ **intl-tel-input** | Международный ввод телефона с флагами стран |
| ✅ **Выбор способа связи** | Telegram, WhatsApp, телефон |
| ✅ **PDF генерация** | Автоматическое создание PDF заказа |
| ✅ **Email уведомления** | Отправка PDF администратору |
| ✅ **Скидки** | Поддержка старой и новой цены |
| ✅ **Заявки** | Форма для присоединения к команде |
| ✅ **Сертификаты** | Оформление заказов на сертификаты |

### 🛠 Технические особенности

- 🔧 **jQuery** — JavaScript библиотека
- 🔧 **Google Charts** — визуализация статистики в админке
- 🔧 **DomPDF** — генерация PDF документов
- 🔧 **WordPress AJAX API** — асинхронная обработка запросов
- 🔧 **Composer** — управление зависимостями
- 🔧 **Session & LocalStorage** — гибридное хранение данных

---

## 📁 Структура плагина

```
cart-plugin/
├── assets/                      # Ресурсы плагина
│   ├── cart.css                # Основные стили корзины
│   ├── cart.js                 # JavaScript логика корзины
│   ├── fonts/                  # Шрифты (Despair Display)
│   ├── vendor/                 # Сторонние библиотеки
│   │   └── intl-tel-input/    # Ввод международного телефона
│   ├── cart-icon.svg           # Иконка пустой корзины
│   └── cart-icon-full.svg      # Иконка полной корзины
├── includes/                    # Основные файлы логики
│   ├── settings.php            # Настройки и меню админки
│   ├── handle-order.php        # Обработка заказов и PDF
│   ├── post-types.php          # Регистрация типов записей
│   ├── render-cart.php         # HTML шаблон корзины
│   ├── personalities-modal-render.php  # Шаблон формы заявки
│   └── cart-settings-page.php  # Страница настроек
├── cart-plugin.php             # Главный файл плагина
├── autoload.php                # Автозагрузчик классов
├── composer.json               # Зависимости Composer
└── composer.lock               # Заблокированные версии
```

---

## 📋 Требования

| Компонент | Минимальная версия | Рекомендуемая версия |
|-----------|-------------------|---------------------|
| WordPress | 5.0+ | 6.0+ |
| PHP | 7.4+ | 8.3+ |
| MySQL | 5.7+ | 8.4+ |
| ACF Pro | 5.0+ | 6.0+ |

### Необходимые PHP расширения:
- `gd` или `imagick` — для обработки изображений в PDF
- `mbstring` — для работы с многобайтовыми строками
- `xml` — для DomPDF

---

## 🚀 Установка

### Шаг 1: Скачивание и распаковка

Скачайте плагин и распакуйте архив в директорию:

```bash
wp-content/plugins/cart-plugin/
```

### Шаг 2: Установка зависимостей

Перейдите в папку плагина и установите зависимости через Composer:

```bash
cd wp-content/plugins/cart-plugin/
composer install --no-dev
```

### Шаг 3: Активация плагина

1. Перейдите в админ-панель WordPress: **Плагины → Установленные плагины**
2. Найдите **"Cart Plugin +"**
3. Нажмите **"Активировать"**

### Шаг 4: Первоначальная настройка

После активации в меню появится пункт **"Заказы и Заявки"**:

1. Перейдите в **Заказы и Заявки → Настройки**
2. Укажите email администратора
3. Выберите тип записи для товаров
4. Настройте маппинг полей ACF

---

## ⚙️ Настройка плагина

### Страница настроек

Доступ: **Заказы и Заявки → Настройки**

#### Основные настройки

| Поле | Описание |
|------|----------|
| **Почта администратора** | Email для получения уведомлений о заказах |
| **Тип записи** | CPT для отображения товаров (например, `products`) |
| **Страница политики конфиденциальности** | Страница с текстом политики |

#### Маппинг полей ACF

Настройте соответствие полей ACF для карточки товара:

| Поле | Назначение | Тип |
|------|-----------|-----|
| **Картинка** | Изображение товара | Image Field |
| **Название** | Название товара | Text Field |
| **Объём** | Размер/объём товара | Text Field |
| **Цена** | Текущая цена | Number Field |
| **Старая цена** | Цена до скидки | Number Field |
| **Бренд** | Таксономия бренда | Taxonomy |
| **Категория товара** | Таксономия категории | Taxonomy |

---

## 📝 Пользовательские типы записей

Плагин автоматически регистрирует следующие типы записей:

| Тип записи | Slug | Описание | Статус |
|------------|------|----------|--------|
| **Заказы** | `cart_order` | Заказы из корзины товаров | Private |
| **Заявки** | `personalities_order` | Заявки на присоединение к команде | Private |
| **Сертификаты** | `certificates_order` | Заказы на сертификаты | Private |

Все типы записей имеют статус **Private** и доступны только администраторам.

---

## 🏷 Таксономии

### Источник заказа (`order_source`)

Иерархическая таксономия для категоризации заказов по источникам:

- **Привязка:** `cart_order`, `personalities_order`, `certificates_order`
- **Отображение в админке:** Да
- **ЧПУ:** Отключено

#### Примеры источников:
- `barbershop` — Барбершоп
- `tattoo` — Тату-салон
- `academy` — Академия
- `cleaning` — Химчистка

---

## 🔌 AJAX API

Плагин использует WordPress AJAX API для всех операций с корзиной.

### Доступные действия

#### 1. Добавление товара в корзину

```javascript
jQuery.ajax({
    url: cart_ajax_object.ajaxurl,
    type: 'POST',
    data: {
        action: 'add_to_cart',
        product_id: 123,
        acf_fields_nonce: cart_ajax_object.nonce
    },
    success: function(response) {
        if (response.success) {
            console.log('Товар добавлен');
        }
    }
});
```

#### 2. Получение деталей товаров

```javascript
jQuery.ajax({
    url: cart_ajax_object.ajaxurl,
    type: 'POST',
    data: {
        action: 'get_cart_product_details',
        ids: JSON.stringify([1, 2, 3]),
        nonce: cart_ajax_object.nonce
    },
    success: function(response) {
        if (response.success) {
            // response.data содержит массив товаров
        }
    }
});
```

#### 3. Оформление заказа

```javascript
jQuery.ajax({
    url: cart_ajax_object.ajaxurl,
    type: 'POST',
    data: {
        action: 'submit_cart_order',
        nonce: cart_ajax_object.nonce,
        cart: JSON.stringify(cartData),
        name: 'Имя клиента',
        phone: '+79990000000',
        contact_method: 'telegram'
    },
    success: function(response) {
        if (response.success) {
            alert('Заказ успешно создан!');
        }
    }
});
```

#### 4. Оформление заявки (персоналии)

```javascript
jQuery.ajax({
    url: personalities_ajax_object.ajaxurl,
    type: 'POST',
    data: {
        action: 'submit_personalities_order',
        nonce: personalities_ajax_object.nonce,
        name: 'Имя кандидата',
        phone: '+79990000000',
        contact_method: 'whatsapp',
        taxonomy: ['barbershop', 'barbershop'],
        request_type: 'join',
        product_name: ''
    }
});
```

---

## 📄 Генерация PDF

Плагин автоматически генерирует PDF-документы для каждого заказа используя библиотеку **DomPDF**.

### Структура PDF документа

1. **Заголовок** — номер заказа
2. **Информация о заказе:**
   - Категория/Источник
   - Имя клиента
   - Телефон
   - Способ связи
3. **Список товаров:**
   - Изображение
   - Название и объём
   - Бренд
   - Цена (со скидкой если есть)
   - Количество
4. **Итоговая сумма** — с указанием скидки если применимо

### Хранение PDF файлов

PDF файлы сохраняются в директорию:

```
wp-content/uploads/cart-orders/
```

Формат именования:
- `order-{ID}.pdf` — заказы товаров
- `join-{ID}.pdf` — заявки на присоединение
- `certificate-{ID}.pdf` — заказы сертификатов

### Автоматическая очистка

При удалении заказа из админки соответствующий PDF файл автоматически удаляется с сервера.

---

## 📊 Dashboard виджеты

Плагин добавляет интерактивный виджет на главную страницу WordPress Admin Dashboard.

### Виджет "Статистика заказов и заявок"

Включает:

1. **Гистограммы по каждому типу записей:**
   - Распределение заказов по источникам
   - Распределение заявок по источникам
   - Распределение сертификатов по источникам

2. **Круговая диаграмма:**
   - Общая статистика по типам записей

3. **Столбчатая диаграмма:**
   - Распределение по всем источникам

### Технологии визуализации

- **Google Charts** — библиотека для построения графиков
- **ColumnChart** — столбчатые диаграммы
- **PieChart** — круговые диаграммы

---

## 💻 Frontend интеграция

### Подключение корзины на странице

Для отображения кнопки корзины добавьте в свой шаблон:

```php
<?php
// Иконка корзины со счётчиком
?>
<div class="cart-icon-wrapper">
    <img src="<?php echo plugin_dir_url(__FILE__); ?>assets/cart-icon.svg" 
         class="cart-trigger" alt="Корзина">
    <span class="cart-count-badge"></span>
</div>
```

### Обновление счётчика корзины

```javascript
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    const count = Object.values(cart).reduce((sum, item) => sum + item.qty, 0);
    jQuery('.cart-count-badge').text(count);
}
```

### Кнопка "Добавить в корзину"

```html
<button class="add-to-cart-btn" data-product-id="123">
    Добавить в корзину
</button>

<script>
jQuery(document).ready(function($) {
    $('.add-to-cart-btn').on('click', function() {
        const productId = $(this).data('product-id');
        $.ajax({
            url: cart_ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_to_cart',
                product_id: productId,
                acf_fields_nonce: cart_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateCartCount();
                    alert('Товар добавлен в корзину');
                }
            }
        });
    });
});
</script>
```

---

## 🎨 CSS структура

### Основные классы

| Класс | Описание |
|-------|----------|
| `.cart-modal-overlay` | Затемнение фона при открытой корзине |
| `.cart-modal` | Основное модальное окно корзины |
| `.cart-modal-header` | Шапка корзины с заголовком и кнопкой закрытия |
| `.cart-items-list` | Список товаров в корзине |
| `.cart-item` | Отдельный товар в списке |
| `.cart-total-amount` | Итоговая сумма |
| `.cart-total-sale` | Сумма со скидкой |
| `.cart-checkout` | Кнопка оформления заказа |
| `.contact-methods` | Переключатели способов связи |

### Анимации

```css
@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
```

Корзина появляется справа с плавной анимацией длительностью **0.3s**.

---

## 🔧 Разработка и расширение

### Добавление нового типа заявки

1. Зарегистрируйте новый тип записи в `settings.php`:

```php
register_post_type('your_custom_order', [
    'labels' => [
        'name' => 'Ваши заказы',
        'singular_name' => 'Ваш заказ',
    ],
    'public' => false,
    'show_ui' => true,
    'supports' => ['title'],
]);
```

2. Добавьте обработчик в `handle-order.php`:

```php
function your_custom_handle_order($data) {
    // Ваша логика обработки
    $order_id = wp_insert_post([...]);
    return $order_id;
}
```

3. Создайте AJAX обработчик в `cart-plugin.php`:

```php
add_action('wp_ajax_your_custom_action', 'your_custom_handler');
add_action('wp_ajax_nopriv_your_custom_action', 'your_custom_handler');
```

### Изменение шаблона PDF

Отредактируйте HTML в функции `cart_handle_order()` в файле `handle-order.php`:

```php
$html = '<div style="ваш-кастомный-стиль">';
$html .= '<h1>Ваш заголовок</h1>';
// ... ваш HTML
```

### Добавление новых полей в форму

1. Отредактируйте `render-cart.php` для добавления HTML полей
2. Обновите `cart.js` для сбора данных из новых полей
3. Модифицируйте `handle-order.php` для обработки новых данных

---

## 🐛 Отладка

### Логирование ошибок

Плагин ведёт лог ошибок в файле:

```
wp-content/plugins/cart-plugin/debug-log.txt
```

Для включения логирования проверьте настройки в `cart-plugin.php`:

```php
ini_set('log_errors', 1);
ini_set('error_log', plugin_dir_path(__FILE__) . 'debug-log.txt');
```

### Отладка PDF

Для сохранения HTML версии заказа (перед генерацией PDF) раскомментируйте строку в `handle-order.php`:

```php
file_put_contents(WP_CONTENT_DIR . '/order_debug.html', $html);
```

---

## 📞 Контакты и поддержка

| Параметр | Значение |
|----------|----------|
| **Автор** | Kenny |
| **Версия** | 1.1 |
| **Лицензия** | Все права защищены |
| **Совместимость** | Tattoo Theme |

---

## 📝 Changelog

### Version 1.1 (Current)
✅ Интеграция с intl-tel-input для международного ввода телефона  
✅ Улучшена генерация PDF с изображениями товаров  
✅ Добавлены Dashboard виджеты со статистикой  
✅ Оптимизирована работа с localStorage  
✅ Добавлена поддержка заявок на сертификаты  
✅ Улучшена валидация форм  

### Version 1.0
✅ Первоначальный релиз  
✅ Базовая функциональность корзины  
✅ AJAX добавление товаров  
✅ Генерация PDF заказов  
✅ Email уведомления  
✅ Маппинг ACF полей  

---

## 📄 Лицензия

Cart Plugin + распространяется под лицензией GPL-2.0+.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

## 🔗 Полезные ссылки

- [Документация WordPress AJAX](https://codex.wordpress.org/AJAX_in_Plugins)
- [DomPDF Documentation](https://dompdf.github.io/)
- [Advanced Custom Fields](https://www.advancedcustomfields.com/resources/)
- [Google Charts](https://developers.google.com/chart)

---

Made with ❤️ by Kenny
