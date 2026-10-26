<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$config = get_app_config();

$url = trim($input['google_script_url'] ?? $config['google_script_url'] ?? '');

if (empty($url)) {
    echo json_encode([
        'success' => false,
        'message' => 'ยังไม่ได้ระบุ Google Apps Script Web App URL'
    ]);
    exit;
}

$test_url = $url . (strpos($url, '?') !== false ? '&' : '?') . 'action=status&t=' . time();

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

if ($curl_err) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $curl_err
    ]);
    exit;
}

$data = json_decode($response, true);
if ($data && !empty($data['success'])) {
    echo json_encode([
        'success'      => true,
        'message'      => 'เชื่อมต่อกับ Google Sheet สำเร็จ!',
        'details'      => $data
    ]);
} else {
    echo json_encode([
        'success'      => false,
        'message'      => 'Google Apps Script ตอบกลับแต่รูปแบบไม่ถูกต้อง กรุณาตรวจสอบว่าได้ตั้งค่า Who has access เป็น Anyone แล้วหรือไม่',
        'raw_response' => substr($response, 0, 300)
    ]);
}
