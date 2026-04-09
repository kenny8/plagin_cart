<?php
function handle_add_to_cart() {
    if (!isset($_POST['acf_fields_nonce']) || !wp_verify_nonce($_POST['acf_fields_nonce'], 'add_to_cart_nonce')) {
        wp_send_json_error(['message' => 'Неверный nonce']);
    }

    if (!isset($_POST['product_id'])) {
        wp_send_json_error(['message' => 'ID товара не передан']);
    }

    $product_id = intval($_POST['product_id']);
    $post_type = get_option('cart_post_type');
    $product = get_post($product_id);

    if (!$product || $product->post_type !== $post_type) {
        wp_send_json_error(['message' => 'Товар не найден или тип не совпадает']);
    }

    // Инициализация корзины
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Увеличиваем количество, если товар уже есть
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }

    error_log('Updated cart: ' . print_r($_SESSION['cart'], true));

    wp_send_json_success([
        'message' => 'Товар добавлен в корзину',
        'product_title' => $product->post_title
    ]);

    wp_die();
}


add_action('wp_ajax_add_to_cart', 'handle_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'handle_add_to_cart');
