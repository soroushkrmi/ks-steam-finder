<?php
if (!defined('ABSPATH')) {
    exit;
}

// =========================================================
// 1. هسته اسکرپر (Scraper)
// =========================================================
if (!class_exists('Khasteshop_Steam_Scraper_Public')) {
    class Khasteshop_Steam_Scraper_Public {
        private $country = 'us';
        private $lang = 'english';

        public function __construct($country = 'us') {
            $this->country = $country;
        }

        public function get_dlc_list($appId) {
            $list = [];
            
            // گام اول: استفاده از موتور هوشمند DOMDocument به جای Regex
            $url_store = "https://store.steampowered.com/app/$appId/?cc={$this->country}&l={$this->lang}";
            $html_store = $this->request($url_store);
            
            if ($html_store) {
                $dom = new DOMDocument();
                // نادیده گرفتن ارورهای HTML ناقص استیم
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html_store, LIBXML_NOERROR | LIBXML_NOWARNING);
                $xpath = new DOMXPath($dom);
                
                // پیدا کردن تمام ردیف‌های بازی
                $rows = $xpath->query('//a[contains(@class, "game_area_dlc_row") and @data-ds-appid]');
                foreach ($rows as $row) {
                    $id = $row->getAttribute('data-ds-appid');
                    $nameNode = $xpath->query('.//div[contains(@class, "game_area_dlc_name")]', $row)->item(0);
                    
                    if ($nameNode) {
                        // پیدا کردن جعبه حاوی لیبل (مثل Recommended) و حذف فیزیکی آن از حافظه
                        $highlight = $xpath->query('.//div[contains(@class, "dlc_highlight_reason_container")]', $nameNode)->item(0);
                        if ($highlight) {
                            $nameNode->removeChild($highlight);
                        }
                        
                        // حالا هرچه باقی مانده، فقط و فقط اسم خالص بازی است
                        $name = trim($nameNode->textContent);
                        if (!empty($name)) {
                            $list[] = ['id' => $id, 'name' => $name, 'type' => 'dlc'];
                        }
                    }
                }
            }

            // گام دوم: رهگیری دقیق در حالت AJAX (پشتیبان برای بازی‌های بزرگ)
            $url_ajax = "https://store.steampowered.com/dlc/$appId/ajax/dlc_expanded?cc={$this->country}&l={$this->lang}";
            $raw_ajax = $this->request($url_ajax);
            
            if ($raw_ajax) {
                $json = json_decode($raw_ajax, true);
                $html_ajax = isset($json['content_html']) ? $json['content_html'] : $raw_ajax;
                
                if ($html_ajax) {
                    $dom_ajax = new DOMDocument();
                    @$dom_ajax->loadHTML('<?xml encoding="utf-8" ?>' . $html_ajax, LIBXML_NOERROR | LIBXML_NOWARNING);
                    $xpath_ajax = new DOMXPath($dom_ajax);
                    
                    $rows_ajax = $xpath_ajax->query('//a[@data-ds-appid]');
                    foreach ($rows_ajax as $row) {
                        $id = $row->getAttribute('data-ds-appid');
                        $nameNode = $xpath_ajax->query('.//div[contains(@class, "game_name")]', $row)->item(0);
                        
                        if ($nameNode) {
                            $highlight = $xpath_ajax->query('.//div[contains(@class, "dlc_highlight_reason_container")]', $nameNode)->item(0);
                            if ($highlight) {
                                $nameNode->removeChild($highlight);
                            }
                            $name = trim($nameNode->textContent);
                            if (!empty($name)) {
                                $list[] = ['id' => $id, 'name' => $name, 'type' => 'dlc'];
                            }
                        }
                    }
                }
            }

            // ادغام هر دو نتیجه و پاک کردن آیتم‌های تکراری
            $unique_list = [];
            foreach ($list as $item) {
                $unique_list[$item['id']] = $item;
            }
            
            return array_values($unique_list);
        }

        public function get_bundle_list($appId) {
            $url = "https://store.steampowered.com/app/$appId/?cc={$this->country}&l={$this->lang}";
            $html = $this->request($url);
            $bundles_found = [];
            if ($html) {
                $ids = [];
                if (preg_match_all('/data-ds-bundleid="(\d+)"/s', $html, $matches)) foreach($matches[1] as $bid) $ids[] = $bid;
                if (preg_match_all('/\/bundle\/(\d+)\//', $html, $matches)) foreach($matches[1] as $bid) $ids[] = $bid;
                $ids = array_unique($ids);
                foreach ($ids as $bid) {
                    $name = '';
                    if (preg_match('/data-ds-bundleid="'.$bid.'".*?<h1>(.*?)<\/h1>/s', $html, $m)) {
                        $name = trim(strip_tags($m[1]));
                        $name = str_replace('Buy ', '', $name);
                    }
                    if (empty($name) || stripos($name, 'Bundle info') !== false || strlen($name) < 3) {
                        $name = $this->fetch_bundle_name_direct($bid);
                    }
                    $bundles_found[] = ['id' => $bid, 'name' => $name, 'type' => 'bundle'];
                }
            }
            return array_values($bundles_found);
        }

        private function fetch_bundle_name_direct($bundleId) {
            $url = "https://store.steampowered.com/bundle/$bundleId/?cc={$this->country}&l={$this->lang}";
            $html = $this->request($url); 
            if ($html) {
                if (preg_match('/<title>(.*?) on Steam<\/title>/', $html, $m)) {
                    $title = $m[1];
                    $title = preg_replace('/^Save \d+% on /i', '', $title);
                    return trim($title);
                }
                if (preg_match('/<h2 class="pageheader">(.*?)<\/h2>/', $html, $m)) return trim($m[1]);
            }
            return "Bundle #$bundleId";
        }

        public function get_product_info($id, $type) {
            if ($type == 'bundle') return $this->fetch_bundle($id);
            if ($type == 'sub') return $this->fetch_package($id);
            return $this->fetch_app($id);
        }

        private function fetch_app($id) {
            $url = "https://store.steampowered.com/api/appdetails?appids=$id&cc={$this->country}&l={$this->lang}&filters=basic,price_overview,type";
            $json = $this->request($url);
            $data = json_decode($json, true);
            if (isset($data[$id]['success']) && $data[$id]['success']) {
                $d = $data[$id]['data'];
                return [
                    'success' => true,
                    'name' => $d['name'],
                    'image' => $d['header_image'] ?? "https://cdn.cloudflare.steamstatic.com/steam/apps/$id/header.jpg",
                    'price_data' => $d['price_overview'] ?? ($d['is_free'] ? 'free' : false),
                    'type' => $d['type']
                ];
            }
            return ['success' => false, 'msg' => 'اطلاعات یافت نشد.'];
        }

        private function fetch_package($id) {
            $url = "https://store.steampowered.com/api/packagedetails?packageids=$id&cc={$this->country}&l={$this->lang}";
            $json = $this->request($url);
            if ($json) {
                $data = json_decode($json, true);
                if (isset($data[$id]['success']) && $data[$id]['success']) {
                    $d = $data[$id]['data'];
                    return [
                        'success' => true,
                        'name' => $d['name'],
                        'image' => 'https://store.steampowered.com/public/images/v6/app/game_header_image_full.jpg',
                        'price_data' => $d['price'] ?? false,
                        'type' => 'sub'
                    ];
                }
            }
            return ['success' => false, 'msg' => 'پکیج یافت نشد.'];
        }

        private function fetch_bundle($id) {
            $url = "https://store.steampowered.com/bundle/$id/?cc={$this->country}&l={$this->lang}";
            $html = $this->request($url);
            if ($html) {
                $data = ['success' => true, 'type' => 'bundle'];
                if (preg_match('/<h2 class="pageheader">(.*?)<\/h2>/', $html, $m)) $data['name'] = trim($m[1]);
                elseif (preg_match('/<title>(.*?) on Steam<\/title>/', $html, $m)) {
                     $t = $m[1]; $t = preg_replace('/^Save \d+% on /i', '', $t);
                     $data['name'] = trim($t);
                } else $data['name'] = "Bundle #$id";

                $data['image'] = "https://cdn.cloudflare.steamstatic.com/steam/bundles/$id/header.jpg";
                if (preg_match('/data-price-final="(\d+)"/', $html, $m)) {
                    $final = intval($m[1]);
                    $initial = $final;
                    $discount = 0;
                    if (preg_match('/data-price-initial="(\d+)"/', $html, $mi)) $initial = intval($mi[1]);
                    if($initial > $final) $discount = round(100 - ($final / $initial * 100));
                    $data['price_data'] = ['final' => $final, 'initial' => $initial, 'discount_percent' => $discount];
                } else {
                    return ['success' => false, 'msg' => 'قیمت باندل پیدا نشد.'];
                }
                return $data;
            }
            return ['success' => false, 'msg' => 'صفحه باندل پیدا نشد.'];
        }

        private function request($url) {
            $response = wp_remote_get($url, ['timeout' => 20, 'sslverify' => false, 'cookies' => ['birthtime' => '786240001', 'steamCountry' => strtoupper($this->country) . '%7C00']]);
            if (is_wp_error($response)) return null;
            return wp_remote_retrieve_body($response);
        }
    }
}