<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$config = get_app_config();

$backup_file = __DIR__ . '/../data/donations_backup.json';
$total_local = 0;
if (file_exists($backup_file)) {
    $raw = @file_get_contents($backup_file);
    if ($raw) {
        $arr = json_decode($raw, true);
        if (is_array($arr)) {
            $total_local = count($arr);
        }
    }
}

echo json_encode([
    'success' => true,
    'config'  => [
        'google_script_url' => $config['google_script_url'] ?? '',
        'google_sheet_url'  => $config['google_sheet_url'] ?? '',
        'hospital_name'     => $config['hospital_name'] ?? '',
        'account_number'    => $config['account_number'] ?? '',
        'total_local'       => $total_local
    ]
]);
