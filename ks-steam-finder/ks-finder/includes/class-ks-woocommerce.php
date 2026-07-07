<?php
if (!defined('ABSPATH')) {
    exit;
}

// =========================================================
// 3. سیستم اتصال به سبد خرید (فیکس شده برای رفع لودینگ)
// =========================================================

add_action('wp_ajax_ks_public_add_cart', 'ks_public_add_cart');
add_action('wp_ajax_nopriv_ks_public_add_cart', 'ks_public_add_cart');

function ks_public_add_cart() {
    check_ajax_referer('ks_robot_nonce', 'security');
    $product_id = 587; // ID محصول مجازی شما
    
    // دریافت داده‌ها
    $cart_item_data = [
        'steam_custom_data' => [
            'name' => sanitize_text_field($_POST['title']), // نام بازی
            'price' => floatval($_POST['price']),
            'steam_user' => sanitize_text_field($_POST['steam_user']),
            'steam_pass' => sanitize_text_field($_POST['steam_pass']),
            'region' => sanitize_text_field($_POST['region']),
            'url' => sanitize_text_field($_POST['url']),
            'steam_id' => sanitize_text_field($_POST['steam_id']),
            'image' => sanitize_text_field($_POST['image']), // عکس بازی
            'steam_guard_status' => 'خاموش',
            'terms_accepted' => 'بله',
            'unique_key' => md5(microtime() . rand()) 
        ]
    ];

    WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);
    wp_send_json_success(['redirect' => wc_get_cart_url()]); 
}

// 1. بازیابی اطلاعات از سشن (فیکس اصلی مشکل لودینگ)
add_filter('woocommerce_get_cart_item_from_session', 'ks_restore_session_data', 20, 2);
function ks_restore_session_data($cart_item, $values) {
    if (isset($values['steam_custom_data'])) {
        $cart_item['steam_custom_data'] = $values['steam_custom_data'];
        $cart_item['data']->set_price(floatval($values['steam_custom_data']['price']));
    }
    return $cart_item;
}

// 2. تنظیم قیمت در محاسبات
add_action('woocommerce_before_calculate_totals', 'ks_set_cart_price', 20, 1);
function ks_set_cart_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['steam_custom_data'])) {
            $cart_item['data']->set_price(floatval($cart_item['steam_custom_data']['price']));
        }
    }
}

// 3. تغییر تصویر محصول در سبد خرید به تصویر بازی
add_filter('woocommerce_cart_item_thumbnail', 'ks_custom_thumb', 10, 3);
function ks_custom_thumb($thumbnail, $cart_item, $cart_item_key) {
    if (isset($cart_item['steam_custom_data']['image'])) {
        return '<img src="' . esc_url($cart_item['steam_custom_data']['image']) . '" style="width: 80px; border-radius: 5px;" />';
    }
    return $thumbnail;
}

// 4. نمایش نام محصول اصلی (مجازی) + اطلاعات زیر آن
// طبق درخواست: نام آیتم نباید جایگزین نام محصول شود، بلکه در زیر آن نمایش داده شود.
add_filter('woocommerce_cart_item_name', 'ks_custom_cart_name', 10, 3);
function ks_custom_cart_name($name, $cart_item, $cart_item_key) {
    if (isset($cart_item['steam_custom_data'])) {
        // نام محصول اصلی (مثلا خرید انواع بازی استیم) + استایل
        return sprintf(
            '<a href="%s" style="color:#0073aa; font-weight:bold; font-size:15px; font-family:IRANSans !important;">%s</a>',
            $cart_item['data']->get_permalink(),
            $cart_item['data']->get_name()
        );
    }
    return $name;
}

// 5. نمایش لیست اطلاعات (شناسه، عنوان بازی، ریجن، یوزر، پسورد) در زیر محصول
add_filter('woocommerce_get_item_data', 'ks_cart_item_meta', 10, 2);
function ks_cart_item_meta($item_data, $cart_item) {
    if (isset($cart_item['steam_custom_data'])) {
        $d = $cart_item['steam_custom_data'];
        
        // این بخش دقیقا لیست زیر محصول در سبد خرید را میسازد (طبق عکس image_3b7f27.png)
        $item_data[] = ['name' => 'شناسه استیم', 'value' => $d['steam_id']];
        $item_data[] = ['name' => 'عنوان بازی', 'value' => $d['name']];
        $item_data[] = ['name' => 'منطقه', 'value' => strtoupper($d['region'])];
        $item_data[] = ['name' => 'نام کاربری استیم', 'value' => $d['steam_user']];
        $item_data[] = ['name' => 'پسورد اکانت استیم', 'value' => $d['steam_pass']];
    }
    return $item_data;
}

// 6. ذخیره نهایی در سفارش
add_action('woocommerce_checkout_create_order_line_item', 'ks_save_order_meta', 10, 4);
function ks_save_order_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['steam_custom_data'])) {
        $d = $values['steam_custom_data'];
        
        // در سفارش نهایی هم نام اصلی محصول باشد، و متادیتاها زیرش
        $item->add_meta_data('عنوان بازی (انتخابی)', $d['name']);
        $item->add_meta_data('شناسه استیم', $d['steam_id']);
        $item->add_meta_data('لینک بازی', $d['url']);
        $item->add_meta_data('ریجن', strtoupper($d['region']));
        $item->add_meta_data('User', $d['steam_user']);
        $item->add_meta_data('Pass', $d['steam_pass']);
        $item->add_meta_data('وضعیت گارد', $d['steam_guard_status']);
    }
}