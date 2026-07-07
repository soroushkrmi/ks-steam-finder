<?php
if (!defined('ABSPATH')) {
    exit;
}

// =========================================================
// 4. شورت‌کد (UI) - استایل و مرکز چین کردن پاپ‌آپ
// =========================================================
add_shortcode('ks_robot_final', 'ks_render_robot_final');

function ks_render_robot_final() {
    ob_start();
    ?>
    <style>
        /* فونت و ساختار کلی */
        /* فونت و ساختار کلی اعمال شده برای فرم و پاپ‌آپ */
        .ks-wrap *, .ks-modal-overlay * { 
            box-sizing: border-box; 
            font-family: 'IRANSans', sans-serif !important; 
        }
        .ks-wrap { max-width: 650px; margin: 30px auto; direction: rtl; color: #f3f4f6; }
        
        /* باکس اصلی فرم (تم تاریک مدرن) */
        .ks-box { background: #2c2c2c; padding: 25px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); border: 1px solid #3d3d3d; transition: all 0.3s ease; }
        
        /* انتخابگرهای رادیویی */
        .ks-radios { display: flex; gap: 15px; margin-bottom: 20px; background: #1a1a1a; padding: 12px; border-radius: 12px; border: 1px solid #333; }
        .ks-radio-item { flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; font-weight: bold; color: #a1a1aa; transition: 0.2s; }
        .ks-radio-item:hover { color: #fff; }
        .ks-radio-item input { width: 18px; height: 18px; accent-color: #ff2e2e; cursor: pointer; }

        /* فیلدهای ورودی */
        .ks-label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 13px; color: #d1d5db; }
        .ks-input, .ks-select { width: 100%; padding: 14px; background: #1a1a1a; color: #fff; border: 2px solid #333; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; margin-bottom: 18px; }
        .ks-input:focus, .ks-select:focus { border-color: #ff2e2e; outline: none; box-shadow: 0 0 10px rgba(255, 46, 46, 0.2); }
        .ks-select option { background: #1a1a1a; color: #fff; }
        
        /* دکمه اصلی */
        .ks-btn { width: 100%; padding: 15px; background: #ff2e2e; color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.3s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
        .ks-btn:hover { background: #e62020; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(255, 46, 46, 0.3); }
        .ks-btn:disabled { background: #52525b; color: #a1a1aa; cursor: not-allowed; transform: none; box-shadow: none; }

        /* نتایج جستجو */
        .ks-list-box { display: none; max-height: 300px; overflow-y: auto; border: 1px solid #333; margin-top: 15px; border-radius: 10px; background: #1a1a1a; scrollbar-width: thin; scrollbar-color: #ff2e2e #1a1a1a; }
        .ks-list-box::-webkit-scrollbar { width: 6px; }
        .ks-list-box::-webkit-scrollbar-thumb { background-color: #ff2e2e; border-radius: 10px; }
        .ks-list-item { padding: 15px; border-bottom: 1px solid #2c2c2c; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; font-size: 14px; color: #d1d5db; }
        .ks-list-item:hover { background: #2c2c2c; color: #fff; border-right: 4px solid #ff2e2e; }
        .ks-badge { background: #3f3f46; font-size: 11px; padding: 3px 8px; border-radius: 6px; color: #f4f4f5; margin-left: 8px; font-weight: bold; }

        /* --- استایل پاپ‌آپ (Modal) --- */
        .ks-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 10, 10, 0.85); z-index: 999999; display: none; justify-content: center; align-items: center; backdrop-filter: blur(5px); }
        .ks-modal { background: #2c2c2c; width: 90%; max-width: 420px; border-radius: 18px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border: 1px solid #3d3d3d; animation: ksPopIn 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28); position: relative; margin: auto; color: #fff; }
        @keyframes ksPopIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* هدر مودال */
        .ks-m-head { padding: 20px; text-align: center; border-bottom: 1px solid #3d3d3d; background: #1a1a1a; }
        .ks-m-head img { width: 100%; border-radius: 10px; margin-bottom: 15px; max-height: 180px; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .ks-m-title { font-weight: 800; font-size: 16px; color: #fff; line-height: 1.5; }

        /* بدنه مودال */
        .ks-m-body { padding: 20px; }
        
        /* ردیف‌های اطلاعاتی */
        .ks-info-row { background: #1a1a1a; border: 1px solid #333; border-radius: 10px; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px; color: #a1a1aa; transition: 0.2s; }
        .ks-info-row:hover { border-color: #444; }
        .ks-info-row strong { color: #fff; }
        .ks-price-row { background: rgba(255, 46, 46, 0.05); border: 1px solid rgba(255, 46, 46, 0.3); font-size: 15px; font-weight: bold; }

        /* فیلدهای داخل مودال */
        .ks-input-group { margin-top: 15px; }
        .ks-input-label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 8px; color: #d1d5db; }
        .ks-modal-input { width: 100%; padding: 12px; border: 2px solid #333; border-radius: 10px; font-size: 14px; background: #1a1a1a; color: #fff; direction: ltr; text-align: left; transition: all 0.3s ease; }
        .ks-modal-input:focus { border-color: #ff2e2e; outline: none; box-shadow: 0 0 10px rgba(255, 46, 46, 0.2); }

        /* چک‌باکس‌ها */
        .ks-check-wrap { margin-top: 15px; font-size: 12px; color: #a1a1aa; background: #1a1a1a; padding: 12px; border-radius: 10px; border: 1px solid #333; }
        .ks-check-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .ks-check-row:last-child { margin-bottom: 0; }
        .ks-check-row input { width: 18px; height: 18px; cursor: pointer; accent-color: #ff2e2e; }
        .ks-check-row a { color: #ff2e2e; text-decoration: none; font-weight: bold; transition: 0.2s; }
        .ks-check-row a:hover { text-shadow: 0 0 5px rgba(255, 46, 46, 0.5); }

        /* دکمه‌های تایید و انصراف */
        .ks-m-footer { padding: 15px 20px; display: flex; gap: 12px; border-top: 1px solid #3d3d3d; background: #222; }
        .ks-btn-action { flex: 1; padding: 12px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 14px; transition: all 0.2s ease; }
        .ks-btn-confirm { background: #ff2e2e; color: white; }
        .ks-btn-confirm:hover { background: #e62020; box-shadow: 0 4px 10px rgba(255, 46, 46, 0.3); }
        .ks-btn-cancel { background: #3f3f46; color: #fff; }
        .ks-btn-cancel:hover { background: #52525b; }

    </style>

    <div class="ks-wrap">
        <div class="ks-box">
            <input type="hidden" id="ks_security_token" value="<?php echo wp_create_nonce('ks_robot_nonce'); ?>">
            <div class="ks-radios">
                <label class="ks-radio-item"><input type="radio" name="ks_type" value="dlc" checked> DLC (محتوای اضافه)</label>
                <label class="ks-radio-item"><input type="radio" name="ks_type" value="bundle"> باندل (Bundle)</label>
            </div>
            
            <label class="ks-label">لینک صفحه بازی در استیم:</label>
            <input type="text" id="ks_url" class="ks-input" placeholder="https://store.steampowered.com/app/..." style="direction:ltr; text-align:left;">
            
            <label class="ks-label">ریجن اکانت:</label>
            <select id="ks_region" class="ks-select">
                <option value="try">🇹🇷 ترکیه (Turkey)</option>
                <option value="ars">🇦🇷 آرژانتین (Argentina)</option>
                <option value="uah">🇺🇦 اوکراین (Ukraine)</option>
                <option value="inr">🇮🇳 هند (India)</option>
            </select>
            
            <button id="ks_submit" class="ks-btn">🔎 جستجو</button>
            <div id="ks_msg" style="text-align:center; margin-top:10px; color:#10b981; font-weight:bold;"></div>
            <div id="ks_list_box" class="ks-list-box"></div>
        </div>
    </div>

    <div id="ks_modal" class="ks-modal-overlay">
        <div class="ks-modal">
            
            <div id="ks_step_1">
                <div class="ks-m-head">
                    <img id="ks_m_img" src="">
                    <div id="ks_m_name" class="ks-m-title"></div>
                </div>
                <div class="ks-m-body">
                    <div class="ks-info-row ks-price-row">
                        <span>قیمت نهایی:</span>
                        <span id="ks_m_price" style="color:#16a34a"></span>
                    </div>
                    
                    <div class="ks-input-group">
                        <label class="ks-input-label">نام کاربری استیم:</label>
                        <input type="text" id="ks_u" class="ks-modal-input" readonly onfocus="this.removeAttribute('readonly');" autocomplete="off">
                    </div>
                    <div class="ks-input-group">
                        <label class="ks-input-label">رمز عبور استیم:</label>
                        <input type="text" id="ks_p" class="ks-modal-input" readonly onfocus="this.removeAttribute('readonly');" autocomplete="off">
                    </div>

                    <div class="ks-check-wrap">
                        <div class="ks-check-row">
                            <input type="checkbox" id="ks_check1"> <label for="ks_check1">استیم گارد خاموش است.</label>
                        </div>
                        <div class="ks-check-row">
                            <input type="checkbox" id="ks_check2"> <label for="ks_check2"><a href="https://khasteshop.com/terms-and-conditions/" target="_blank">شرایط و مقررات</a> را می‌پذیرم.</label>
                        </div>
                    </div>
                </div>
                <div class="ks-m-footer">
                    <button id="ks_cancel" class="ks-btn-action ks-btn-cancel">لغو</button>
                    <button id="ks_go_confirm" class="ks-btn-action ks-btn-confirm">ادامه</button>
                </div>
            </div>

            <div id="ks_step_2" style="display:none;">
                <div style="padding:15px; text-align:center; font-weight:bold; border-bottom:1px solid #eee;">تأیید اطلاعات</div>
                <div style="padding:10px;">
                    <img id="ks_conf_img" style="width:100%; border-radius:8px; height:150px; object-fit:cover;">
                </div>
                <div class="ks-m-body" style="padding-top:0;">
                    <div class="ks-info-row"><span>آدرس استیم:</span> <span id="ks_conf_id"></span></div>
                    <div class="ks-info-row"><span>عنوان محصول:</span> <strong id="ks_conf_name_txt"></strong></div>
                    <div class="ks-info-row"><span>منطقه:</span> <span id="ks_conf_region"></span></div>
                    <div class="ks-info-row"><span>نام کاربری:</span> <span id="ks_conf_u" style="direction:ltr"></span></div>
                    <div class="ks-info-row"><span>پسورد:</span> <span id="ks_conf_p" style="direction:ltr"></span></div>
                    <div class="ks-info-row ks-price-row"><span>قیمت:</span> <span id="ks_conf_price" style="color:#16a34a"></span></div>
                </div>
                <div class="ks-m-footer">
                    <button id="ks_back" class="ks-btn-action ks-btn-cancel">لغو / اصلاح</button>
                    <button id="ks_final_add" class="ks-btn-action ks-btn-confirm">افزودن به سبد</button>
                </div>
            </div>

        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var itemData = {};

        // Search
        $('#ks_submit').click(function() {
            var url = $('#ks_url').val();
            if(!url.includes('/app/')) { alert('لینک معتبر نیست'); return; }
            var btn = $(this); btn.prop('disabled',true).text('...');
            $('#ks_list_box').slideUp();
            
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'ks_public_get_list',security: $('#ks_security_token').val(), url: url, type: $('input[name=ks_type]:checked').val(), region: $('#ks_region').val()
            }, function(res) {
                btn.prop('disabled',false).text('🔎 جستجو');
                if(res.success) {
                    $('#ks_msg').text(res.data.count + ' مورد یافت شد');
                    var html = '';
                    res.data.items.forEach(function(i){
                        html += `<div class="ks-list-item" data-id="${i.id}" data-type="${i.type}">
                            <div><span class="ks-badge">${i.type.toUpperCase()}</span> ${i.name}</div>
                            <div>&larr;</div>
                        </div>`;
                    });
                    $('#ks_list_box').html(html).slideDown();
                } else { alert(res.data); }
            });
        });

        // Click Item
        $(document).on('click', '.ks-list-item', function() {
            var id = $(this).data('id'), type = $(this).data('type');
            $('#ks_submit').prop('disabled',true).text('محاسبه...');
            
            // Reset Modal
            $('#ks_step_1').show(); $('#ks_step_2').hide();
            $('#ks_u').val('').attr('readonly', true); 
            $('#ks_p').val('').attr('readonly', true);
            $('#ks_check1, #ks_check2').prop('checked', false);

            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'ks_public_get_item',security: $('#ks_security_token').val(), id: id, type: type, region: $('#ks_region').val()
            }, function(res) {
                $('#ks_submit').prop('disabled',false).text('🔎 جستجو');
                if(res.success) {
                    itemData = res.data;
                    itemData.url = $('#ks_url').val();
                    itemData.region = $('#ks_region').val();
                    
                    // Fill Step 1
                    $('#ks_m_img').attr('src', itemData.image);
                    $('#ks_m_name').text(itemData.name);
                    $('#ks_m_price').text(itemData.toman_display + ' تومان');
                    $('#ks_modal').css('display', 'flex'); // Force Flex Center
                } else { alert(res.data); }
            });
        });

        // Go to Step 2
        $('#ks_go_confirm').click(function() {
            var u = $('#ks_u').val(), p = $('#ks_p').val();
            if(!u || !p) { alert('یوزرنیم و پسورد الزامی است'); return; }
            if(!$('#ks_check1').is(':checked') || !$('#ks_check2').is(':checked')) { alert('لطفا تیک‌ها را بزنید'); return; }

            // Fill Step 2 (Confirmation)
            $('#ks_conf_img').attr('src', itemData.image);
            $('#ks_conf_id').text(itemData.steam_id);
            $('#ks_conf_name_txt').text(itemData.name);
            $('#ks_conf_region').text($('#ks_region option:selected').text());
            $('#ks_conf_u').text(u);
            $('#ks_conf_p').text(p);
            $('#ks_conf_price').text(itemData.toman_display + ' تومان');

            $('#ks_step_1').hide(); $('#ks_step_2').fadeIn();
        });

        $('#ks_back').click(function(){ $('#ks_step_2').hide(); $('#ks_step_1').fadeIn(); });
        $('#ks_cancel').click(function(){ $('#ks_modal').fadeOut(); });

        // Add to Cart
        $('#ks_final_add').click(function() {
            var btn = $(this); btn.prop('disabled',true).text('ثبت...');
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'ks_public_add_cart',security: $('#ks_security_token').val(),
                title: itemData.name, price: itemData.toman_value,
                steam_id: itemData.steam_id, url: itemData.url, region: itemData.region,
                steam_user: $('#ks_u').val(), steam_pass: $('#ks_p').val(), image: itemData.image
            }, function(res) {
                if(res.success) window.location.href = res.data.redirect;
                else { alert('خطا'); btn.prop('disabled',false); }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}