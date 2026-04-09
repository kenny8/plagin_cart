<?php
/*
if (!is_user_logged_in()) session_start();

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$post_type = get_option('cart_post_type');
*/
?>

<div class="cart-modal-header">
    <div class="cart-title-wrapper">
        <span class="cart-title">Корзина</span>
        <?php
        $count = 0;
        foreach ($cart_items as $qty) {
        $count += $qty;
        }

        if ($count > 0): ?>
            <span class="cart-count">(<?php echo $count; ?>)</span>
        <?php endif; ?>
    </div>
    <button class="cart-modal-close"></button>
</div>

<div class="cart-modal-content">
    <?php if (!empty($cart_items)) : ?>
        <ul class="cart-items-list">
            <?php foreach ($cart_items as $product_id => $quantity):
                $product = get_post($product_id);
                if ($product && $product->post_type === $post_type): ?>
                    <li class="cart-item" data-product-id="<?php echo esc_attr($product_id); ?>">
                        <strong><?php echo esc_html($product->post_title); ?></strong>
                        <p>ID: <?php echo $product_id; ?> | Количество: <?php echo $quantity; ?></p>
                        <button class="remove-from-cart">Удалить</button>
                    </li>
                <?php endif;
            endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Корзина пуста.</p>
    <?php endif; ?>
</div>
