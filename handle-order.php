<?php
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Преобразует изображение в Data URL base64.
 */
function image_to_data_url($filename) {
    if (!file_exists($filename)) {
        throw new Exception('File not found: ' . $filename);
    }

    $mime = mime_content_type($filename);
    if ($mime === false) {
        throw new Exception('Unable to determine MIME type.');
    }

    $raw_data = file_get_contents($filename);
    if (empty($raw_data)) {
        throw new Exception('File unreadable or empty.');
    }

    return "data:{$mime};base64," . base64_encode($raw_data);
}

/**
 * Убеждается, что термин существует в таксономии order_source.
 */
function ensure_order_source_term($slug, $name) {
    if (!get_term_by('slug', $slug, 'order_source')) {
        wp_insert_term($name, 'order_source', ['slug' => $slug]);
    }
}

/**
 * Обработчик оформления заказа.
 */
function cart_handle_order($name, $phone, $contact_method, $cart_data) {
    // Получаем количество заказов
    $order_count = wp_count_posts('cart_order')->private;
    $order_number = $order_count + 1; // Номер заказа

    // Создаем заказ
    $order_id = wp_insert_post([
        'post_title'  => 'Заказ №' . $order_number,
        'post_type'   => 'cart_order',
        'post_status' => 'private',
    ]);

    if (is_wp_error($order_id)) {
        return $order_id;
    }

    $mapping = [
        'image'      => get_option('cart_mapping_image_field'),
        'title'      => get_option('cart_mapping_title_field'),
        'volume'     => get_option('cart_mapping_volume_field'),
        'price'      => get_option('cart_mapping_price_field'),
        'old_price'  => get_option('cart_mapping_old_price_field'),
        'brand'      => get_option('cart_mapping_brand_taxonomy'),
        'category'   => get_option('cart_mapping_category_taxonomy'),
    ];

    $first_product_id = array_key_first($cart_data);
    $parent_category_name = get_first_term_name($first_product_id, $mapping['category'], true);
    $parent_category_slug = sanitize_title($parent_category_name);

    ensure_order_source_term($parent_category_slug, $parent_category_name);
    wp_set_object_terms($order_id, $parent_category_slug, 'order_source');

    $order_sources = get_the_terms($order_id, 'order_source');
    $source_name = (!empty($order_sources) && !is_wp_error($order_sources)) ? $order_sources[0]->name : $parent_category_name;

    $total_quantity = $total_price = $total_old_price = 0;


    $html = '<div style="margin: 0; padding: 0; color: #333; word-wrap: break-word;">';

    $html .= '<div style="text-align: center; margin-top: 20px; font-size: 32px; font-weight: bold;">Заказ №' . $order_number . '</div>';
    
    $html .= '<div style="background-color: #fff; padding: 5px; margin: 10px auto; width: 90%; max-width: 700px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">';
    $html .= '<div style="font-size: 24px; font-weight: bold; margin-bottom: 12px; text-align: center;">Информация о заказе</div>';
    
    $html .= '<div style="font-size: 18px; line-height: 1.6; margin-bottom: 20px;">';
    
    // Используем таблицу без линий, но с отступами для разделения
    $html .= '<table style="width: 100%; border-collapse: collapse;">';
    
    // Строка для первой пары данных (Категория / Имя)
    $html .= '<tr>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Категория:</strong> ' . esc_html($source_name) . '</td>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Имя:</strong> ' . esc_html($name) . '</td>';
    $html .= '</tr>';
    
    // Строка для второй пары данных (Телефон / Способ связи)
    $html .= '<tr>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Телефон:</strong> ' . esc_html($phone) . '</td>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Способ связи:</strong> ' . esc_html($contact_method) . '</td>';
    $html .= '</tr>';
    
    $html .= '</table>'; // Закрываем таблицу
    
    $html .= '</div>';
    
    $html .= '</div>';
    
    $html .= '</div>';
    
    

    $html .= '<h2>Товары (' . array_sum(array_map(function($item) { return $item['qty']; }, $cart_data)) . '):</h2>'; // Считаем общее количество товаров
$html .= '<div style="border-top:2px solid #000;margin-top:10px;padding-top:10px;">';

$total_quantity = 0;
$total_price = 0;
$total_old_price = 0;
$has_old_prices = false; 


foreach ($cart_data as $product_id => $item) {
    
    $product_id = (int) $product_id;
    $quantity = (int) ($item['qty'] ?? 1);

    $product_title = get_field($mapping['title'], $product_id) ?: 'Нет названия';
    $volume = get_field($mapping['volume'], $product_id) ?: '-';
    $price = (float) (get_field($mapping['price'], $product_id) ?: 0);
    $old_price_raw = get_field($mapping['old_price'], $product_id);
    $old_price_value = ($old_price_raw !== '' && $old_price_raw !== null) ? (float) $old_price_raw : 0;
    $brand_name = get_first_term_name($product_id, $mapping['brand']) ?: '-';
    $image = get_field($mapping['image'], $product_id);
    $image_url = is_array($image) ? ($image['url'] ?? '') : $image;

    $total_quantity += $quantity;
    $total_price += $price * $quantity;

    if ($old_price_value > 0) {
        $total_old_price += $old_price_value * $quantity;
        $has_old_prices = true; // нашлась старая цена, отмечаем
    } else {
        $total_old_price += $price * $quantity; // если нет старой, берём обычную
    }

    $image_base64 = '';
    if (!empty($image_url)) {
        $image_path = str_replace(home_url(), rtrim(ABSPATH, '/'), $image_url);
        try {
            $image_base64 = image_to_data_url($image_path);
        } catch (Exception $e) {
            $image_base64 = '';
        }
    }

    $html .= '<table style="width:100%;margin-bottom:20px;border-bottom:1px solid #ddd;padding-bottom:10px;">';
    $html .= '<tr>';

    // Картинка
    $html .= '<td style="width:120px;padding:10px;vertical-align:top;">';
    if (!empty($image_base64)) {
        $html .= '<img src="' . $image_base64 . '" style="width:100px;height:100px;object-fit:cover;border-radius:8px;">';
    } else {
        $html .= '<span style="font-size:12px;color:#999;">Нет изображения</span>';
    }
    $html .= '</td>';

    // Название + Бренд
    $html .= '<td style="padding:10px;vertical-align:top;">';
    $html .= '<div style="font-size:16px;font-weight:bold;margin-bottom:6px;">' . esc_html($product_title) . ', ' . esc_html($volume) . '</div>';
    $html .= '<div style="font-size:14px;color:#777;">' . esc_html($brand_name) . '</div>';
    $html .= '</td>';

    // Цена + Кол-во
    $html .= '<td style="padding:10px;text-align:right;vertical-align:top;">';
    if ($old_price_value && $old_price_value > $price) {
        $html .= '<div style="font-size:14px;text-decoration:line-through;color:#999;">' . number_format($old_price_value, 0, '.', ' ') . ' ₽</div>';
    }
    $html .= '<div style="font-size:16px;font-weight:bold;color:#000;margin-top:4px;">' . number_format($price, 0, '.', ' ') . ' ₽</div>';
    $html .= '<div style="font-size:14px;color:#555;margin-top:4px;">× ' . $quantity . '</div>';
    $html .= '</td>';

    $html .= '</tr>';
    $html .= '</table>';
}

$html .= '</div>';
    

$html .= '<div style="width:100%;margin-top:20px;border-top:2px solid #000;padding-top:20px;">';

if ($has_old_prices && $total_old_price > $total_price) {
    $html .= '<div style="width:100%;margin-top:10px;">';
    $html .= '<div style="font-size:16px;font-weight:bold;">Итоговая сумма:</div>';
    
    $html .= '<div style="margin-top: -40px;padding:5px 20px;font-size:16px;text-decoration:line-through;text-align:right;color:#999;">';
    $html .= '<div>без скидки: ' . number_format($total_old_price, 0, '.', ' ') . ' ₽</div>';
    $html .= '</div>';
    
    $html .= '<div style="padding:5px 20px;font-size:18px;font-weight:bold;text-align:right;color:#000;margin-top:4px;">';
    $html .= '<div>со скидкой: ' . number_format($total_price, 0, '.', ' ') . ' ₽</div>';
    $html .= '</div>';
    
    $html .= '</div>';
    
} else {
    // если не было старых цен — или разницы нет
    $html .= '<table style="width:100%;margin-top:10px;">';
    $html .= '<tr>';
    $html .= '<td style="padding:10px 20px;font-size:18px;font-weight:bold;">
                Итоговая сумма: ' . number_format($total_price, 0, '.', ' ') . ' ₽
              </td>';
    $html .= '</tr>';
    $html .= '</table>';
}

$html .= '</div>';

    // Генерация PDF
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/cart-orders';

    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }

    // Сохраняем HTML в файл для отладки
    //file_put_contents(WP_CONTENT_DIR . '/order_debug.html', $html);

    $pdf_path = $pdf_dir . '/order-' . $order_id . '.pdf';
    file_put_contents($pdf_path, $dompdf->output());

    // Отправляем письмо
    $admin_email = get_option('cart_admin_email');
    if ($admin_email) {
        wp_mail(
            $admin_email,
            'Новый заказ №' . $order_number,
            'В приложении находится файл с заказом.',
            ['Content-Type: text/html; charset=UTF-8'],
            [$pdf_path]
        );
    }

    // Сохраняем ссылку на PDF
    update_post_meta($order_id, '_cart_order_pdf', $upload_dir['baseurl'] . '/cart-orders/order-' . $order_id . '.pdf');
}





function personalitie_handle_order($name, $phone, $contact_method, $taxonomy, $request_type, $product_name) {
    // Определяем тип записи (заказ или заявка)
    switch($request_type) {
        case 'certificate':
            $post_type = 'certificates_order';
            $title_prefix = 'Заявка № ';
            break;
        case 'join':
            $post_type = 'personalities_order';
            $title_prefix = 'Заявка № ';
            break;
        case 'order':
        default:
            $post_type = 'cart_order';
            $title_prefix = 'Заказ № ';
            break;
    }
    
    // Получаем количество существующих записей для нумерации
    $order_count = wp_count_posts($post_type)->private;
    $order_number = $order_count + 1;
    
    $post_title = $title_prefix . $order_number;
    
    // Базовые данные для создания записи

    $order_id = wp_insert_post([
        'post_title'  => $post_title,
        'post_type'   => $post_type,
        'post_status' => 'private',
    ]);

    if (is_wp_error($order_id)) {
        return $order_id;
    }


    ensure_order_source_term($taxonomy[0], $taxonomy[1]);
    wp_set_object_terms($order_id, $taxonomy[1], 'order_source');

    $order_sources = get_the_terms($order_id, 'order_source');
    $source_name = (!empty($order_sources) && !is_wp_error($order_sources)) ? $order_sources[0]->name : $taxonomy[1];

    $total_quantity = $total_price = $total_old_price = 0;
    $target = "";
    if ($taxonomy[1] == 'barbershop' && $request_type == 'join') {
        $target = "присoединится к команде барбершопа";
    } elseif ($taxonomy[1] == 'tattoo' && $request_type == 'join') {
        $target = "присoединится к команде татту";
    } elseif ($taxonomy[1] == 'tattoo' && $request_type == 'join' && $product_name !='') {
        $target = "бесплатная консультация - " . $product_name;
    } elseif ($taxonomy[1] == 'academy' && $request_type == 'join') {
        $target = "стать моделью академии";   
    }  elseif ($taxonomy[1] == 'cleaning' && $request_type == 'order') {
        $target = "воспользоватся химчисткой " . $product_name;
    } elseif ($taxonomy[1] == 'academy' && $request_type == 'order') {
        $target = "записатся на курс " . $product_name;
    } elseif ($taxonomy[1] == 'barbershop' && $request_type == 'certificate') {
        $target = "приобрести сертификат " . $product_name;
    } elseif ($taxonomy[1] == 'tattoo' && $request_type == 'certificate') {
        $target = "приобрести сертификат" . $product_name;
    } elseif ($taxonomy[1] == 'cleaning' && $request_type == 'certificate') {
        $target = "приобрести сертификат" . $product_name;
    }  


    $html = '<div style="margin: 0; padding: 0; color: #333; word-wrap: break-word;">';

    $html .= '<div style="text-align: center; margin-top: 20px; font-size: 32px; font-weight: bold;">' . $post_title . '</div>';
    
    $html .= '<div style="background-color: #fff; padding: 5px; margin: 10px auto; width: 90%; max-width: 700px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">';
    $html .= '<div style="font-size: 24px; font-weight: bold; margin-bottom: 12px; text-align: center;">Информация о заказе</div>';
    
    $html .= '<div style="font-size: 18px; line-height: 1.6; margin-bottom: 20px;">';
    
    // Используем таблицу без линий, но с отступами для разделения
    $html .= '<table style="width: 100%; border-collapse: collapse;">';
    
    // Строка для первой пары данных (Категория / Имя)
    $html .= '<tr>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Категория:</strong> ' . esc_html($source_name) . '</td>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Имя:</strong> ' . esc_html($name) . '</td>';
    $html .= '</tr>';
    
    // Строка для второй пары данных (Телефон / Способ связи)
    $html .= '<tr>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Телефон:</strong> ' . esc_html($phone) . '</td>';
    $html .= '<td style="font-size: 16px; padding: 2px 10px; text-align: left; vertical-align: top;"><strong>Способ связи:</strong> ' . esc_html($contact_method) . '</td>';
    $html .= '</tr>';

    $html .= '</table>'; // Закрываем таблицу

    $html .= '<div style="font-size: 16px; text-align: left;"><strong>Цель:</strong> ' . esc_html($target) . '</div>';
    
    $html .= '</div>';
    
    $html .= '</div>';
    
    $html .= '</div>';
    


    // Генерация PDF
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/cart-orders';

    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }
    
    if ($request_type == 'join') {
        $pdf_path = $pdf_dir . '/join-' . $order_id . '.pdf';
    } elseif ($request_type == 'order') {
        $pdf_path = $pdf_dir . '/order-' . $order_id . '.pdf';
    } elseif ($request_type == 'certificate') {
        $pdf_path = $pdf_dir . '/certificate-' . $order_id . '.pdf';
    } else {
        $pdf_path = $pdf_dir . '/other-' . $order_id . '.pdf';
    }

    file_put_contents($pdf_path, $dompdf->output());

    // Отправляем письмо
    $admin_email = get_option('cart_admin_email');
    if ($admin_email) {
        wp_mail(
            $admin_email,
            'Новый заявка №' . $order_number,
            'В приложении находится файл с заказом.',
            ['Content-Type: text/html; charset=UTF-8'],
            [$pdf_path]
        );
    }

    // Сохраняем ссылку на PDF
        if ($request_type == 'join') {
        update_post_meta($order_id, '_personalities_order_pdf', $upload_dir['baseurl'] . '/cart-orders/join-' . $order_id . '.pdf');
    } elseif ($request_type == 'order') {
        $pdf_path = $pdf_dir . '/order-' . $order_id . '.pdf';
        update_post_meta($order_id, '_cart_order_pdf', $upload_dir['baseurl'] . '/cart-orders/order-' . $order_id . '.pdf');
    } elseif ($request_type == 'certificate') {
        $pdf_path = $pdf_dir . '/certificate-' . $order_id . '.pdf';
        update_post_meta($order_id, '_certificates_order_pdf', $upload_dir['baseurl'] . '/cart-orders/certificate-' . $order_id . '.pdf');
    } else {
        $pdf_path = $pdf_dir . '/other-' . $order_id . '.pdf';
        update_post_meta($order_id, '_cart_order_pdf', $upload_dir['baseurl'] . '/cart-orders/other-' . $order_id . '.pdf');
    }
}
