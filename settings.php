<?php
// Регистрация меню "Корзина" и подменю "Заказы" и "Настройки"
add_action('admin_menu', function () {
    add_menu_page(
        'Заказы и Заявки',           // Название страницы
        'Заказы и Заявки',           // Название меню
        'manage_options',    
        'cart_orders',       
        'cart_orders_page',  
        'dashicons-cart',    
        25
    );

    add_submenu_page(
        'cart_orders',
        'Заказы',
        'Заказы',
        'manage_options',
        'edit.php?post_type=cart_order'
    );

    add_submenu_page(
        'cart_orders',
        'Заявки',
        'Заявки',
        'manage_options',
        'edit.php?post_type=personalities_order'
    );

    add_submenu_page(
        'cart_orders',
        'Сертификаты',
        'Сертификаты',
        'manage_options',
        'edit.php?post_type=certificates_order'
    );

    add_submenu_page(
        'cart_orders',
        'Источники заказа',
        'Источники заказа',
        'manage_options',
        'edit-tags.php?taxonomy=order_source&post_type=cart_order'
    );

    add_submenu_page(
        'cart_orders',
        'Настройки корзины',
        'Настройки',
        'manage_options',
        'cart_settings',
        'cart_settings_page'
    );

   

    // Убираем автосозданный подменю "Корзина"
    remove_submenu_page('cart_orders', 'cart_orders');
}, 999); // <-- здесь правильно закрывается admin_menu

// Регистрируем тип записи "Заказ" и таксономию "Источник заказа"
add_action('init', function () {
    register_post_type('cart_order', [
        'labels' => [
            'name' => 'Заказы',
            'singular_name' => 'Заказ',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // Покажем вручную в подменю
        'supports' => ['title'], // Только заголовок
        'capability_type' => 'post',
    ]);

    register_post_type('personalities_order', [
        'labels' => [
            'name' => 'Заявки',
            'singular_name' => 'Заявка',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'supports' => ['title'],
        'capability_type' => 'post',
    ]);

    register_post_type('certificates_order', [
        'labels' => [
            'name' => 'Сертификаты',
            'singular_name' => 'Сертификат',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'supports' => ['title'],
        'capability_type' => 'post',
    ]);

    register_taxonomy('order_source', ['cart_order', 'personalities_order', 'certificates_order'], [
        'labels' => [
            'name' => 'Источники заказа',
            'singular_name' => 'Источник заказа',
            'search_items' => 'Поиск источников',
            'all_items' => 'Все источники',
            'edit_item' => 'Редактировать источник',
            'update_item' => 'Обновить источник',
            'add_new_item' => 'Добавить новый источник',
            'new_item_name' => 'Название нового источника',
            'menu_name' => 'Источники заказа',
        ],
        'hierarchical' => true,      // Иерархия нужна
        'show_ui' => true,            // Показывать интерфейс
        'show_admin_column' => true,  // Показать колонку в списке заказов
        'rewrite' => false,           // ЧПУ не нужен
    ]);
});

// Страница "Настройки корзины"
function cart_settings_page() {
    $file_path = plugin_dir_path(__FILE__) . 'cart-settings-page.php';
    include $file_path;
}

// Добавление колонки "Скачать PDF" в список заказов
add_filter('manage_cart_order_posts_columns', function ($columns) {
    $columns['download_pdf'] = 'Скачать PDF';
    return $columns;
});

// Заполнение данных в колонке "Скачать PDF"
add_action('manage_cart_order_posts_custom_column', function ($column, $post_id) {
    if ($column === 'download_pdf') {
        $pdf_url = get_post_meta($post_id, '_cart_order_pdf', true); // Предполагается, что ты сохраняешь путь в мета
        if ($pdf_url) {
            echo '<a href="' . esc_url($pdf_url) . '" class="button" target="_blank">Скачать</a>';
        } else {
            echo 'Нет файла';
        }
    }
}, 10, 2);

add_filter('manage_personalities_order_posts_columns', function ($columns) {
    $columns['download_pdf'] = 'Скачать PDF';
    return $columns;
});

add_action('manage_personalities_order_posts_custom_column', function ($column, $post_id) {
    if ($column === 'download_pdf') {
        $pdf_url = get_post_meta($post_id, '_personalities_order_pdf', true);
        if ($pdf_url) {
            echo '<a href="' . esc_url($pdf_url) . '" class="button" target="_blank">Скачать</a>';
        } else {
            echo 'Нет файла';
        }
    }
}, 10, 2);

add_filter('manage_certificates_order_posts_columns', function ($columns) {
    $columns['download_pdf'] = 'Скачать PDF';
    return $columns;
});

add_action('manage_certificates_order_posts_custom_column', function ($column, $post_id) {
    if ($column === 'download_pdf') {
        $pdf_url = get_post_meta($post_id, '_certificates_order_pdf', true);
        if ($pdf_url) {
            echo '<a href="' . esc_url($pdf_url) . '" class="button" target="_blank">Скачать</a>';
        } else {
            echo 'Нет файла';
        }
    }
}, 10, 2);


// Когда отправляется в корзину
/*
add_action('before_delete_post', 'cart_order_delete_pdf_file');

function cart_order_delete_pdf_file($post_id) {
    if (get_post_type($post_id) !== 'cart_order') {
        return;
    }

    $meta_value = get_post_meta($post_id, '_cart_order_pdf', true);

    // Защита от пустого значения
    if (empty($meta_value)) {
        error_log('[Удаление заказа] PDF не найден в мета-данных');
        return;
    }

    $pdf_filename = basename($meta_value); // Извлекаем только имя файла
    $uploads_dir = wp_upload_dir();
    $pdf_path = $uploads_dir['basedir'] . '/cart-orders/' . $pdf_filename;

    error_log('[Удаление заказа] PDF-файл: ' . $pdf_path);

    if (file_exists($pdf_path)) {
        unlink($pdf_path);
        error_log('[Удаление заказа] Удалён: ' . $pdf_path);
    } else {
        error_log('[Удаление заказа] Файл не найден');
    }

    // Также удалим мета-данные (можно и позже, WP может сам)
    delete_post_meta($post_id, '_cart_order_pdf');
}

*/
add_filter('wp_count_posts', function($counts, $type, $perm) {
    if ($type === 'cart_order' || $type === 'personalities_order' || $type === 'certificates_order') {
        global $wpdb;
        $counts = (object) array_fill_keys(get_post_stati(), 0);
        $counts->publish = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish'",
            $type
        ));
        $counts->private = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = 'private'",
            $type
        ));
    }
    return $counts;
}, 10, 3);

add_action('before_delete_post', 'cart_order_delete_pdf_file');

function cart_order_delete_pdf_file($post_id) {
    $post_type = get_post_type($post_id);
    
    // Определяем настройки в зависимости от типа записи
    $config = [
        'cart_order' => [
            'meta_key' => '_cart_order_pdf',
            'log_prefix' => '[Удаление заказа]',
            'file_prefix' => 'order'
        ],
        'personalities_order' => [
            'meta_key' => '_personalities_order_pdf',
            'log_prefix' => '[Удаление заявки]',
            'file_prefix' => 'request'
        ],
        'certificates_order' => [
            'meta_key' => '_personalities_order_pdf',
            'log_prefix' => '[Удаление заявки]',
            'file_prefix' => 'request'
        ]
    ];

    // Проверяем поддерживаемый тип записи
    if (!isset($config[$post_type])) {
        return;
    }

    $conf = $config[$post_type];
    $meta_value = get_post_meta($post_id, $conf['meta_key'], true);

    // Логирование попытки удаления
    error_log($conf['log_prefix'] . ' Начало обработки пост ID: ' . $post_id);

    if (empty($meta_value)) {
        error_log($conf['log_prefix'] . ' PDF не найден в мета-данных');
        return;
    }

    $pdf_filename = basename($meta_value);
    $uploads_dir = wp_upload_dir();
    $pdf_path = $uploads_dir['basedir'] . '/orders-pdf/' . $pdf_filename;

    // Логирование информации о файле
    error_log($conf['log_prefix'] . ' Путь к PDF: ' . $pdf_path);
    error_log($conf['log_prefix'] . ' Имя файла: ' . $pdf_filename);

    if (file_exists($pdf_path)) {
        if (unlink($pdf_path)) {
            error_log($conf['log_prefix'] . ' Файл успешно удален');
        } else {
            error_log($conf['log_prefix'] . ' Ошибка при удалении файла');
        }
    } else {
        error_log($conf['log_prefix'] . ' Файл не найден на сервере');
    }

    // Удаляем мета-данные
    if (delete_post_meta($post_id, $conf['meta_key'])) {
        error_log($conf['log_prefix'] . ' Мета-данные удалены');
    } else {
        error_log($conf['log_prefix'] . ' Ошибка удаления мета-данных');
    }
}



/**
 * Исправляем подсчёт записей для приватных типов постов в таксономиях
 */
add_filter('get_terms', 'fix_private_post_terms_count', 20, 2);
function fix_private_post_terms_count($terms, $taxonomies) {
    // Проверяем, что это наша таксономия
    if (!in_array('order_source', $taxonomies)) {
        return $terms;
    }

    // Получаем все наши приватные посты
    $private_posts = get_posts([
        'post_type' => ['cart_order', 'personalities_order', 'certificates_order'],
        'post_status' => 'private',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);

    // Если нет постов - возвращаем как есть
    if (empty($private_posts)) {
        return $terms;
    }

    // Получаем все связи терминов с нашими постами
    global $wpdb;
    $object_terms = $wpdb->get_results("
        SELECT term_taxonomy_id, COUNT(object_id) as count
        FROM {$wpdb->term_relationships}
        WHERE object_id IN (" . implode(',', $private_posts) . ")
        GROUP BY term_taxonomy_id
    ");

    // Создаём массив [term_taxonomy_id => count]
    $term_counts = [];
    foreach ($object_terms as $row) {
        $term_counts[$row->term_taxonomy_id] = $row->count;
    }

    // Обновляем счётчики в терминах
    foreach ($terms as $term) {
        if (isset($term_counts[$term->term_taxonomy_id])) {
            $term->count = $term_counts[$term->term_taxonomy_id];
        }
    }

    return $terms;
}

/**
 * Обновляем счётчики при сохранении поста
 */
add_action('save_post', 'update_order_source_term_count', 10, 2);
function update_order_source_term_count($post_id, $post) {
    if (!in_array($post->post_type, ['cart_order', 'personalities_order', 'certificates_order'])) {
        return;
    }

    $terms = wp_get_object_terms($post_id, 'order_source');
    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            wp_update_term_count_now([$term->term_id], 'order_source');
        }
    }
}




?>
