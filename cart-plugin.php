<?php
/*
Plugin Name: Cart Plugin +
Description: Корзина для кастомных типов записей с ACF и работы с заявками. Отправляет PDF на почту администратора.
Version: 1.1
Author: Kenny
*/

// Логирование ошибок
ini_set('log_errors', 1);
ini_set('error_log', plugin_dir_path(__FILE__) . 'debug-log.txt');

// Подключение основных файлов плагина
include_once plugin_dir_path(__FILE__) . 'includes/settings.php';        // Настройки плагина
include_once plugin_dir_path(__FILE__) . 'includes/handle-order.php';    // Обработка заказа
include_once plugin_dir_path(__FILE__) . 'includes/post-types.php';    // Обработка заказа
include_once plugin_dir_path(__FILE__) . 'includes/render-cart.php';     // Рендер корзины 
include_once plugin_dir_path(__FILE__) . 'includes/personalities-modal-render.php';
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
register_activation_hook(__FILE__, function() {
    $upload_dir = wp_upload_dir();
    $orders_dir = $upload_dir['basedir'] . '/cart-orders';

    if (!file_exists($orders_dir)) {
        wp_mkdir_p($orders_dir); // безопасное создание папки в WordPress
    }
});
// Функция для получения брейкпоинтов
function get_theme_breakpoints() {
    return array(
        'tablet' => absint(get_theme_mod('breakpoint_tablet', 1024)),
        'mobile' => absint(get_theme_mod('breakpoint_mobile', 375))
    );
}

// Подключаем стили и скрипты
add_action('wp_enqueue_scripts', function () {
    // Путь к папке шрифтов
    $font_url = plugin_dir_url(__FILE__) . 'assets/fonts/';

    // Добавляем основной стиль плагина
    wp_enqueue_style('cart-plugin-style', plugin_dir_url(__FILE__) . 'assets/cart.css');

    // Добавляем inline стили для шрифтов
    wp_add_inline_style('cart-plugin-style', "
        @font-face {
            font-family: 'Despair Display';
            src: url('{$font_url}DespairDisplay-Bold.woff2') format('woff2'),
                 url('{$font_url}DespairDisplay-Bold.woff') format('woff');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
    ");

    // Стили для intl-tel-input
    wp_enqueue_style(
        'intl-tel-input-style',
        plugin_dir_url(__FILE__) . 'assets/vendor/intl-tel-input/intlTelInput.css'
    );
    
    // Основной скрипт intlTelInput с утилитами
    wp_enqueue_script(
        'intl-tel-input-script',
        plugin_dir_url(__FILE__) . 'assets/vendor/intl-tel-input/intlTelInputWithUtils.min.js', // Используем эту версию
        [], // Зависимости
        null, // Версия
        true  // Загружаем в футере
    );

    // Подключаем скрипт плагина
    wp_enqueue_script(
        'cart-plugin-script',
        plugin_dir_url(__FILE__) . 'assets/cart.js',
        ['jquery', 'intl-tel-input-script'],
        false,
        true
    );
    
    // Передаем брейкпоинты в скрипт
    wp_localize_script('cart-plugin-script', 'themeBreakpoints', get_theme_breakpoints());

    // Локализуем объект для передачи пути плагина
    wp_localize_script('cart-plugin-script', 'cart_ajax_object', array(
        'ajaxurl'    => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('add_to_cart_nonce'),
        'pluginUrl'  => plugin_dir_url(__FILE__),
        'icons'      => array(
            'empty' => plugin_dir_url(__FILE__) . 'assets/cart-icon.svg',
            'full'  => plugin_dir_url(__FILE__) . 'assets/cart-icon-full.svg',
        )
    ));

    // Дополнительно для формы персоналий
    wp_localize_script('cart-plugin-script', 'personalities_ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('personalities_order_nonce')
));
});




// Подключаем скрипты для админки
add_action('admin_enqueue_scripts', 'cart_enqueue_admin_scripts');
function cart_enqueue_admin_scripts($hook) {
    if ($hook == 'toplevel_page_cart-settings') {
        wp_enqueue_script('cart-plugin-admin-script', plugin_dir_url(__FILE__) . 'assets/cart.js', [], false, true);
    }
}

add_action('wp_ajax_get_cart_product_details', 'get_cart_product_details');
add_action('wp_ajax_nopriv_get_cart_product_details', 'get_cart_product_details');

function get_cart_product_details() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'add_to_cart_nonce')) {
        wp_send_json_error('Неверный nonce');
    }

    $mapping = [
        'image'      => get_option('cart_mapping_image_field'),
        'title'      => get_option('cart_mapping_title_field'),
        'volume'     => get_option('cart_mapping_volume_field'),
        'price'      => get_option('cart_mapping_price_field'),
        'old_price'  => get_option('cart_mapping_old_price_field'),
        'brand'      => get_option('cart_mapping_brand_taxonomy'),    // таксономия бренда
        'category'   => get_option('cart_mapping_category_taxonomy'), // таксономия категорий
    ];

    if (isset($_POST['ids'])) {
        $product_ids = json_decode(stripslashes($_POST['ids']), true);
        if (!is_array($product_ids)) {
            wp_send_json_error('Неверный формат данных');
        }

        $product_ids = array_map('intval', $product_ids);
        $products = [];

        foreach ($product_ids as $product_id) {
            $title      = get_field($mapping['title'], $product_id);
            $price      = get_field($mapping['price'], $product_id);
            $old_price  = get_field($mapping['old_price'], $product_id);
            $volume     = get_field($mapping['volume'], $product_id);
            $image      = get_field($mapping['image'], $product_id);

            $image_url  = $image ? (is_array($image) ? $image['url'] : $image) : '';

            $brand_name          = get_first_term_name($product_id, $mapping['brand']);
            $parent_category_name = get_first_term_name($product_id, $mapping['category'], true);

            $products[] = [
                'id'        => $product_id,
                'title'     => $title,
                'price'     => $price,
                'old_price' => $old_price,
                'volume'    => $volume,
                'brand'     => $brand_name,
                'category'  => $parent_category_name,
                'image'     => $image_url,
            ];
        }

        wp_send_json_success($products);
    } else {
        wp_send_json_error('Нет ID товаров');
    }

    wp_die();
}

/**
 * Получить название первого термина для поста
 *
 * @param int $post_id
 * @param string $taxonomy
 * @param bool $only_parent
 * @return string
 */
function get_first_term_name($post_id, $taxonomy, $only_parent = false) {
    if (empty($taxonomy)) {
        return '';
    }

    $terms = get_the_terms($post_id, $taxonomy);
    if (is_wp_error($terms) || empty($terms)) {
        return '';
    }

    foreach ($terms as $term) {
        if ($only_parent && $term->parent != 0) {
            continue;
        }
        return $term->name;
    }

    return !$only_parent && !empty($terms) ? $terms[0]->name : '';
}




add_action('wp_ajax_submit_cart_order', 'cart_process_order');
add_action('wp_ajax_nopriv_submit_cart_order', 'cart_process_order');

function cart_process_order() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'add_to_cart_nonce')) {
        wp_send_json_error('Неверный nonce');
    }

    $cart_raw = $_POST['cart'] ?? '';
    $cart_data = json_decode(stripslashes($cart_raw), true); // <-- РАСПАКОВЫВАЕМ JSON

    $name = sanitize_text_field($_POST['name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $contact_method = sanitize_text_field($_POST['contact_method'] ?? '');

    if (empty($cart_data) || !is_array($cart_data)) {
        wp_send_json_error('Пустая корзина');
    }

    $order_id = cart_handle_order($name, $phone, $contact_method, $cart_data);

    if (is_wp_error($order_id)) {
        wp_send_json_error($order_id->get_error_message());
    }

    wp_send_json_success([
        'message' => 'Заказ успешно создан!',
        'order_id' => $order_id,
    ]);
}


add_action('wp_ajax_submit_personalities_order', 'handle_personalities_order');
add_action('wp_ajax_nopriv_submit_personalities_order', 'handle_personalities_order');

function handle_personalities_order() {
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'personalities_order_nonce')) {
        wp_send_json_error('Неверный nonce', 403);
    }

    // Получаем и санитизируем данные
    $name = sanitize_text_field($_POST['name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $contact_method = sanitize_text_field($_POST['contact_method'] ?? '');
    $taxonomy = isset($_POST['taxonomy']) ? array_map('sanitize_text_field', (array)$_POST['taxonomy']) : [];
    $request_type = sanitize_text_field($_POST['request_type'] ?? '');
    $product_name = sanitize_text_field($_POST['product_name'] ?? '');

    // Специальная обработка для заказов тату-оборудования
    if ($taxonomy === ['tattoo', 'tattoo'] && $request_type === 'order') {
        $cart_data = [$product_name => ['qty' => 1]];
        $order_id = cart_handle_order($name, $phone, $contact_method, $cart_data);
    } 
    // Обработка остальных случаев
    else {
        $order_id = personalitie_handle_order(
            $name,
            $phone,
            $contact_method,
            $taxonomy,
            $request_type,
            $product_name
        );
    }

    if (is_wp_error($order_id)) {
        wp_send_json_error($order_id->get_error_message());
    }

    wp_send_json_success([
        'message' => 'Заказ успешно создан!',
        'order_id' => $order_id,
    ]);
}

// Добавляем виджет с гистограммами в консоль
add_action('wp_dashboard_setup', 'add_custom_dashboard_stats_widget');

function add_custom_dashboard_stats_widget() {
    if (!current_user_can('manage_options')) return;
    
    wp_add_dashboard_widget(
        'custom_post_type_stats',
        'Статистика заказов и заявок',
        'render_custom_stats_widget'
    );
}

function render_custom_stats_widget() {
    // Массив с типами записей для анализа
    $post_types = [
        'cart_order' => 'Заказы',
        'personalities_order' => 'Заявки',
        'certificates_order' => 'Сертификаты'
    ];
    
    // Получаем статистику
    $stats = get_custom_post_type_stats($post_types);
    
    // Подключаем Google Charts
    echo '<script src="https://www.gstatic.com/charts/loader.js"></script>';
    echo '<style>
        .custom-stats-widget {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        .custom-stats-widget h3 {
            margin: 20px 0 10px;
            font-size: 16px;
            color: #1d2327;
        }
        .chart-container {
            height: 300px;
            margin-bottom: 30px;
            background: #fff;
            border: 1px solid #dcdcde;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .no-data {
            padding: 20px;
            text-align: center;
            color: #646970;
            font-style: italic;
            background: #f6f7f7;
        }
    </style>';
    
    echo '<div class="custom-stats-widget">';
    
    // 1. Выводим гистограммы для каждого типа записей
    foreach ($stats['post_types'] as $type => $data) {
        echo '<h3>' . esc_html($data['label']) . ' (всего: ' . $data['total'] . ')</h3>';
        
        if ($data['total'] > 0 && !empty($data['sources'])) {
            echo '<div id="chart_' . esc_attr($type) . '" class="chart-container"></div>';
            
            $chart_data = [['Источник', 'Количество']];
            foreach ($data['sources'] as $source => $count) {
                $chart_data[] = [esc_js($source), $count];
            }
            
            echo '<script>
                google.charts.load("current", {packages:["corechart"]});
                google.charts.setOnLoadCallback(function() {
                    var data = google.visualization.arrayToDataTable(' . json_encode($chart_data) . ');
                    var options = {
                        title: "Распределение по источникам",
                        legend: { position: "none" },
                        colors: ["#2271b1"],
                        hAxis: { title: "Источники", titleTextStyle: { color: "#50575e" } },
                        vAxis: { 
                            title: "Количество", 
                            minValue: 0,
                            titleTextStyle: { color: "#50575e" }
                        },
                        backgroundColor: "transparent",
                        chartArea: { width: "80%", height: "70%" }
                    };
                    var chart = new google.visualization.ColumnChart(
                        document.getElementById("chart_' . esc_attr($type) . '")
                    );
                    chart.draw(data, options);
                });
            </script>';
        } else {
            echo '<div class="no-data">Нет данных для отображения</div>';
        }
    }
    
    // 2. Общая статистика по типам записей
    echo '<h3>Общая статистика по типам записей</h3>';
    
    $total_data = [['Тип записи', 'Количество']];
    $has_data = false;
    foreach ($stats['post_types'] as $type => $data) {
        if ($data['total'] > 0) {
            $total_data[] = [esc_js($data['label']), $data['total']];
            $has_data = true;
        }
    }
    
    if ($has_data) {
        echo '<div id="chart_total" class="chart-container"></div>';
        echo '<script>
            google.charts.load("current", {packages:["corechart"]});
            google.charts.setOnLoadCallback(function() {
                var data = google.visualization.arrayToDataTable(' . json_encode($total_data) . ');
                var options = {
                    title: "Всего записей по типам",
                    pieHole: 0.4,
                    colors: ["#2271b1", "#d63638", "#f6c342"],
                    backgroundColor: "transparent",
                    chartArea: { width: "80%", height: "70%" }
                };
                var chart = new google.visualization.PieChart(
                    document.getElementById("chart_total")
                );
                chart.draw(data, options);
            });
        </script>';
    } else {
        echo '<div class="no-data">Нет данных для отображения</div>';
    }
    
    // 3. Общая статистика по таксономиям (источникам)
    if (!empty($stats['taxonomy_stats'])) {
        echo '<h3>Общая статистика по источникам</h3>';
        echo '<div id="chart_taxonomy" class="chart-container"></div>';
        
        $tax_data = [['Источник', 'Количество']];
        foreach ($stats['taxonomy_stats'] as $source => $count) {
            $tax_data[] = [esc_js($source), $count];
        }
        
        echo '<script>
            google.charts.load("current", {packages:["corechart"]});
            google.charts.setOnLoadCallback(function() {
                var data = google.visualization.arrayToDataTable(' . json_encode($tax_data) . ');
                var options = {
                    title: "Распределение по всем источникам",
                    legend: { position: "none" },
                    colors: ["#00a32a"],
                    hAxis: { title: "Источники", titleTextStyle: { color: "#50575e" } },
                    vAxis: { 
                        title: "Количество", 
                        minValue: 0,
                        titleTextStyle: { color: "#50575e" }
                    },
                    backgroundColor: "transparent",
                    chartArea: { width: "80%", height: "70%" }
                };
                var chart = new google.visualization.ColumnChart(
                    document.getElementById("chart_taxonomy")
                );
                chart.draw(data, options);
            });
        </script>';
    }
    
    echo '</div>'; // .custom-stats-widget
}

// Функция сбора статистики
function get_custom_post_type_stats($post_types) {
    $stats = [
        'post_types' => [],
        'taxonomy_stats' => []
    ];
    
    // Собираем данные по всем источникам
    $all_sources = [];
    
    foreach ($post_types as $type => $label) {
        // Получаем общее количество записей (используем WP_Query для приватных типов)
        $count_query = new WP_Query([
            'post_type' => $type,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'private',
            'suppress_filters' => false // Важно для приватных типов
        ]);
        $total = $count_query->post_count;
        
        // Получаем распределение по источникам
        $sources = [];
        $terms = get_terms([
            'taxonomy' => 'order_source',
            'hide_empty' => false
        ]);
        
        foreach ($terms as $term) {
            $source_count = new WP_Query([
                'post_type' => $type,
                'posts_per_page' => -1,
                'fields' => 'ids',
                'post_status' => 'private',
                'suppress_filters' => false,
                'tax_query' => [
                    [
                        'taxonomy' => 'order_source',
                        'field' => 'term_id',
                        'terms' => $term->term_id
                    ]
                ]
            ]);
            
            if ($source_count->post_count > 0) {
                $sources[$term->name] = $source_count->post_count;
                
                // Суммируем для общей статистики
                if (!isset($all_sources[$term->name])) {
                    $all_sources[$term->name] = 0;
                }
                $all_sources[$term->name] += $source_count->post_count;
            }
        }
        
        $stats['post_types'][$type] = [
            'label' => $label,
            'total' => $total,
            'sources' => $sources
        ];
    }
    
    // Добавляем общую статистику по таксономиям
    if (!empty($all_sources)) {
        arsort($all_sources); // Сортируем по убыванию
        $stats['taxonomy_stats'] = $all_sources;
    }
    
    return $stats;
}