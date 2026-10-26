<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$config = get_app_config();
$google_url = trim($config['google_script_url'] ?? '');

$wishes = [];

// Try to fetch from Google Sheet if URL exists
if (!empty($google_url)) {
    $fetch_url = $google_url . (strpos($google_url, '?') !== false ? '&' : '?') . 'action=wishes&t=' . time();
    $ch = curl_init($fetch_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);

    if ($res) {
        $parsed = json_decode($res, true);
        if ($parsed && !empty($parsed['success']) && !empty($parsed['wishes'])) {
            echo json_encode([
                'success' => true,
                'source'  => 'google_sheet',
                'wishes'  => $parsed['wishes']
            ]);
            exit;
        }
    }
}

// Fallback to local backup file
$backup_file = __DIR__ . '/../data/donations_backup.json';
if (file_exists($backup_file)) {
    $raw = @file_get_contents($backup_file);
    if ($raw) {
        $data = json_decode($raw, true) ?: [];
        foreach (array_reverse($data) as $row) {
            if (($row['allow_social_post'] ?? '') === 'อนุญาต' && !empty($row['message'])) {
                $wishes[] = [
                    'time'    => $row['created_at'] ?? '',
                    'name'    => $row['donor_name'] ?? 'ผู้มีจิตศรัทธา',
                    'message' => $row['message'],
                    'image1'  => !empty($row['local_image1']) ? $row['local_image1'] : '',
                    'image2'  => !empty($row['local_image2']) ? $row['local_image2'] : ''
                ];
                if (count($wishes) >= 20) break;
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'source'  => 'local_backup',
    'wishes'  => $wishes
]);
