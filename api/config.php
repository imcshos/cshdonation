<?php
/**
 * Configuration for CSH Donation Web Application
 */

define('CONFIG_FILE', __DIR__ . '/../data/config.json');

function get_app_config() {
    $defaults = [
        'google_script_url' => '', // URL ที่ได้จากการ Deploy Google Apps Script Web App
        'google_sheet_url'  => '', // URL สำหรับคลิกไปดู Google Sheet
        'hospital_name'     => 'โรงพยาบาลเชียงแสน',
        'hospital_subtitle' => 'Chiang Saen Hospital · e-Donation',
        'bank_name'         => 'ธนาคารกรุงไทย · สาขาเชียงแสน',
        'account_name'      => 'เงินบริจาค โรงพยาบาลเชียงแสน',
        'account_number'    => '546-0-34327-5',
        'hospital_phone'    => '053-777-017 ต่อ 133(ในเวลาราชการ) ต่อ 156(นอกเวลาราชการ)',
        'hospital_address'  => '104 หมู่ 6 ต.เวียง อ.เชียงแสน จ.เชียงราย 57150',
        'qr_image'          => 'images/donation-qr.jpg',
        'logo_image'        => 'images/hospital.png',
        'admin_username'    => 'cshos',
        'admin_password'    => 'cshos'
    ];

    if (file_exists(CONFIG_FILE)) {
        $json = @file_get_contents(CONFIG_FILE);
        if ($json) {
            $data = json_decode($json, true);
            if (is_array($data)) {
                return array_merge($defaults, $data);
            }
        }
    }

    return $defaults;
}

function save_app_config($data) {
    $current = get_app_config();
    $updated = array_merge($current, $data);
    if (!is_dir(dirname(CONFIG_FILE))) {
        @mkdir(dirname(CONFIG_FILE), 0777, true);
    }
    return @file_put_contents(CONFIG_FILE, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
