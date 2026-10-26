<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$script_url = trim($input['google_script_url'] ?? '');
$sheet_url  = trim($input['google_sheet_url'] ?? '');

$update = [];
if (isset($input['google_script_url'])) {
    $update['google_script_url'] = $script_url;
}
if (isset($input['google_sheet_url'])) {
    $update['google_sheet_url'] = $sheet_url;
}

if (save_app_config($update)) {
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกการตั้งค่า Google Sheet เรียบร้อยแล้ว'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่สามารถบันทึกไฟล์คอนฟิกได้ โปรดตรวจสอบสิทธิ์โฟลเดอร์ data/'
    ]);
}
