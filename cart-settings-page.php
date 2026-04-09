<?php
/*echo '<p>Тестовое сообщение из cart-settings-page.php</p>';*/

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    update_option('cart_admin_email', sanitize_email($_POST['cart_admin_email']));
    update_option('cart_post_type', sanitize_text_field($_POST['cart_post_type']));

    // Сохраняем маппинг полей
    update_option('cart_mapping_image_field', sanitize_text_field($_POST['mapping_image_field']));
    update_option('cart_mapping_title_field', sanitize_text_field($_POST['mapping_title_field']));
    update_option('cart_mapping_volume_field', sanitize_text_field($_POST['mapping_volume_field']));
    update_option('cart_mapping_price_field', sanitize_text_field($_POST['mapping_price_field']));
    update_option('cart_mapping_old_price_field', sanitize_text_field($_POST['mapping_old_price_field']));
    update_option('cart_mapping_brand_taxonomy', sanitize_text_field($_POST['mapping_brand_taxonomy']));
    update_option('cart_mapping_category_taxonomy', sanitize_text_field($_POST['mapping_category_taxonomy']));


    // Сохраняем страницу для политики конфиденциальности
    update_option('cart_privacy_policy_page', sanitize_text_field($_POST['cart_privacy_policy_page']));


    echo '<div class="updated"><p>Настройки сохранены!</p></div>';
}

// Получение текущих настроек
$admin_email = get_option('cart_admin_email');
$post_type = get_option('cart_post_type');
$privacy_policy_page = get_option('cart_privacy_policy_page');
$mapping = [
    'image'      => get_option('cart_mapping_image_field'),
    'title'      => get_option('cart_mapping_title_field'),
    'volume'     => get_option('cart_mapping_volume_field'),
    'price'      => get_option('cart_mapping_price_field'),
    'old_price'  => get_option('cart_mapping_old_price_field'),
    'brand'      => get_option('cart_mapping_brand_taxonomy'),
    'category'      => get_option('cart_mapping_category_taxonomy'),
];

// Получение типов записей
$post_types = get_post_types(['public' => true], 'objects');

// Получение тестовой записи
$posts = get_posts([
    'post_type' => $post_type,
    'posts_per_page' => 1
]);
$pages = get_pages();
?>

<div class="wrap">
    <h1>Настройки корзины</h1>
    <form method="POST">
        <table class="form-table">
            <tr>
                <th><label for="cart_admin_email">Почта администратора</label></th>
                <td>
                    <input type="email" id="cart_admin_email" name="cart_admin_email" value="<?php echo esc_attr($admin_email); ?>" class="regular-text" />
                    <p class="description">На эту почту будут отправляться заказы.</p>
                </td>
            </tr>
            <tr>
                <th><label for="cart_post_type">Тип записи</label></th>
                <td>
                    <select id="cart_post_type" name="cart_post_type">
                        <option value="">— Выберите тип —</option>
                        <?php foreach ($post_types as $key => $type): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($post_type, $key); ?>>
                                <?php echo esc_html($type->labels->singular_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Тип записи для отображения товаров.</p>
                </td>
            </tr>

            <!-- Добавляем выпадающий список для выбора страницы политики конфиденциальности -->
            <tr>
                <th><label for="cart_privacy_policy_page">Страница политики конфиденциальности</label></th>
                <td>
                    <select id="cart_privacy_policy_page" name="cart_privacy_policy_page">
                        <option value="">— Выберите страницу —</option>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($privacy_policy_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Выберите страницу для политики конфиденциальности.</p>
                </td>
            </tr>
        </table>


        <?php if (!empty($posts)): ?>
            <?php
                $post_id = $posts[0]->ID;
                $acf_fields = function_exists('get_fields') ? get_fields($post_id) : [];
                $taxonomies = get_object_taxonomies(get_post_type($post_id), 'objects');
                $fields_list = array_keys($acf_fields);

                function render_select($label, $name, $fields, $selected_value) {
                    echo "<tr><th>{$label}</th><td><select name='mapping_{$name}'>";
                    echo "<option value=''>— Не выбрано —</option>";
                    foreach ($fields as $field) {
                        $selected = selected($selected_value, $field, false);
                        echo "<option value='" . esc_attr($field) . "' $selected>$field</option>";
                    }
                    echo "</select></td></tr>";
                }
            ?>

            <h2>Маппинг полей карточки</h2>
            <p>Выберите, какие поля ACF будут использоваться для каждой части карточки товара.</p>

            <table class="form-table">
                <?php
                    render_select('Картинка',     'image_field',     $fields_list, $mapping['image']);
                    render_select('Название',     'title_field',     $fields_list, $mapping['title']);
                    render_select('Объём',        'volume_field',    $fields_list, $mapping['volume']);
                    render_select('Цена',         'price_field',     $fields_list, $mapping['price']);
                    render_select('Старая цена',  'old_price_field', $fields_list, $mapping['old_price']);
                ?>

                <tr>
                    <th>Бренд (таксономия)</th>
                    <td>
                        <select name="mapping_brand_taxonomy">
                            <option value="">— Не выбрано —</option>
                            <?php foreach ($taxonomies as $tax_key => $tax): ?>
                                <option value="<?php echo esc_attr($tax_key); ?>" <?php selected($mapping['brand'], $tax_key); ?>>
                                    <?php echo esc_html($tax->labels->singular_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
    <th>Категория товара (таксономия)</th>
    <td>
        <select name="mapping_category_taxonomy">
            <option value="">— Не выбрано —</option>
            <?php foreach ($taxonomies as $tax_key => $tax): ?>
                <option value="<?php echo esc_attr($tax_key); ?>" <?php selected($mapping['category'], $tax_key); ?>>
                    <?php echo esc_html($tax->labels->singular_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
</tr>
            </table>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>


<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
jQuery(document).ready(function($) {
    $("#acf-field-sortable").sortable({
        update: function(event, ui) {
            let order = $(this).children().map(function() {
                return $(this).data("field");
            }).get().join(',');
            $("#fields_order").val(order);
        }
    }).disableSelection();

    // При загрузке страницы — сразу выставить порядок
    let initialOrder = $("#acf-field-sortable").children().map(function() {
        return $(this).data("field");
    }).get().join(',');
    $("#fields_order").val(initialOrder);
});
</script>
