<?php
if (!defined('ABSPATH')) {
    exit;
}

// =========================================================
// 2. هندلرهای AJAX (لیست و آیتم)
// =========================================================

add_action('wp_ajax_ks_public_get_list', 'ks_public_get_list');
add_action('wp_ajax_nopriv_ks_public_get_list', 'ks_public_get_list');

function ks_public_get_list() {
    check_ajax_referer('ks_robot_nonce', 'security');

    $input = sanitize_text_field($_POST['url']);
    // --- سیستم امنیتی: جلوگیری از اسپم (Rate Limiting) ---
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $transient_name = 'ks_spam_protect_' . md5($user_ip);
    $request_count = get_transient($transient_name);

    if ($request_count !== false && $request_count > 5) { // محدودیت: 5 درخواست
        wp_send_json_error('ترافیک شما غیرعادی است. لطفاً ۱ دقیقه صبر کنید و دوباره امتحان کنید.');
    }
    
    // ثبت یا آپدیت تعداد درخواست‌ها با انقضای 60 ثانیه
    set_transient($transient_name, ($request_count === false ? 1 : $request_count + 1), 60);
    // -----------------------------------------------------
    $input = sanitize_text_field($_POST['url']);
    $type = sanitize_text_field($_POST['type']);
    $region = sanitize_text_field($_POST['region']);

    $id = 0;
    if (preg_match('/\/app\/(\d+)/', $input, $m)) { $id = $m[1]; }
    elseif (is_numeric($input)) { $id = $input; }

    if(!$id) wp_send_json_error('لینک نامعتبر است.');

    $map = ['try'=>'tr', 'ars'=>'ar', 'uah'=>'ua', 'inr'=>'in'];
    $cc = isset($map[$region]) ? $map[$region] : 'us';

    $scraper = new Khasteshop_Steam_Scraper_Public($cc);
    $list = [];

    if ($type == 'dlc') $list = $scraper->get_dlc_list($id);
    elseif ($type == 'bundle') $list = $scraper->get_bundle_list($id);

    if (empty($list)) wp_send_json_error("موردی یافت نشد.");
    wp_send_json_success(['count' => count($list), 'items' => array_values($list)]);
}

add_action('wp_ajax_ks_public_get_item', 'ks_public_get_item');
add_action('wp_ajax_nopriv_ks_public_get_item', 'ks_public_get_item');

function ks_public_get_item() {
    check_ajax_referer('ks_robot_nonce', 'security');

    $id = sanitize_text_field($_POST['id']);
    // --- سیستم امنیتی: جلوگیری از اسپم (Rate Limiting) ---
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $transient_name = 'ks_spam_protect_' . md5($user_ip);
    $request_count = get_transient($transient_name);

    if ($request_count !== false && $request_count > 5) { // محدودیت: 5 درخواست
        wp_send_json_error('ترافیک شما غیرعادی است. لطفاً ۱ دقیقه صبر کنید و دوباره امتحان کنید.');
    }
    
    // ثبت یا آپدیت تعداد درخواست‌ها با انقضای 60 ثانیه
    set_transient($transient_name, ($request_count === false ? 1 : $request_count + 1), 60);
    // -----------------------------------------------------
    $id = sanitize_text_field($_POST['id']);
    $type = sanitize_text_field($_POST['type']);
    $region = sanitize_text_field($_POST['region']);

    $map = ['try'=>'tr', 'ars'=>'ar', 'uah'=>'ua', 'inr'=>'in'];
    $cc = isset($map[$region]) ? $map[$region] : 'us';

    $scraper = new Khasteshop_Steam_Scraper_Public($cc);
    $result = $scraper->get_product_info($id, $type);
    
    if (!$result['success']) wp_send_json_error('خطا در دریافت اطلاعات.');

    $calc_curr = $region;
    if(in_array($region, ['try', 'ars'])) $calc_curr = 'usd'; 

    $rate = (float) get_option('ks_rate_' . $calc_curr, 0);
    $profit = (float) get_option('ks_profit', 0);
    if($rate <= 0) $rate = (float) get_option('ks_rate_usd', 60000);

    $final_toman = 0; $old_toman = 0; $raw_price = 0; $initial = 0; $discount = 0;

    if ($result['price_data'] === 'free') {
        $raw_price = 0;
    } elseif (is_array($result['price_data'])) {
        $raw_price = $result['price_data']['final'];
        $initial = $result['price_data']['initial'];
        $discount = isset($result['price_data']['discount_percent']) ? $result['price_data']['discount_percent'] : 0;
    }

    if ($rate > 0 && $raw_price > 0) {
        $base = $raw_price / 100;
        $t = $base * $rate;
        if ($profit > 0) $t += ($t * ($profit / 100));
        $final_toman = (round($t / 1000) * 1000);

        if ($discount > 0 && $initial > $raw_price) {
            $base_old = $initial / 100;
            $t_old = $base_old * $rate;
            if ($profit > 0) $t_old += ($t_old * ($profit / 100));
            $old_toman = (round($t_old / 1000) * 1000);
        }
    }

    wp_send_json_success([
        'name' => $result['name'],
        'image' => $result['image'],
        'toman_display' => number_format($final_toman),
        'toman_value' => $final_toman,
        'has_discount' => ($discount > 0),
        'discount_percent' => $discount,
        'old_price_display' => ($old_toman > 0) ? number_format($old_toman) : '',
        'steam_id' => $id,
        'type' => $type
    ]);
}