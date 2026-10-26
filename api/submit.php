<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $donor_name        = trim($_POST['donor_name'] ?? '');
    $message           = trim($_POST['message'] ?? '');
    $receipt_requested = (isset($_POST['receipt_requested']) && $_POST['receipt_requested'] == '1') ? '1' : '0';
    $tax_name          = trim($_POST['tax_name'] ?? '');
    $tax_id            = trim($_POST['tax_id'] ?? '');
    $phone             = trim($_POST['phone'] ?? '');
    $address           = trim($_POST['address'] ?? '');
    $allow_social_post = (isset($_POST['allow_social_post']) && $_POST['allow_social_post'] == '1') ? '1' : '0';

    // Validate receipt fields if requested
    if ($receipt_requested === '1') {
        if (empty($tax_name)) {
            echo json_encode(['success' => false, 'message' => 'กรุณาระบุชื่อ-สกุล หรือชื่อนิติบุคคล สำหรับออกใบอนุโมทนาบัตร']);
            exit;
        }
        if (empty($tax_id)) {
            echo json_encode(['success' => false, 'message' => 'กรุณาระบุเลขบัตรประชาชน หรือเลขผู้เสียภาษี']);
            exit;
        }
        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'กรุณาระบุเบอร์โทรศัพท์สำหรับติดต่อ']);
            exit;
        }
        if (empty($address)) {
            echo json_encode(['success' => false, 'message' => 'กรุณาระบุที่อยู่สำหรับจัดส่งเอกสาร']);
            exit;
        }
    }

    $has_img1 = isset($_FILES['image1']) && $_FILES['image1']['error'] === UPLOAD_ERR_OK;
    $has_img2 = isset($_FILES['image2']) && $_FILES['image2']['error'] === UPLOAD_ERR_OK;

    if (empty($message) && !$has_img1 && !$has_img2 && $receipt_requested !== '1') {
        echo json_encode(['success' => false, 'message' => 'กรุณาระบุข้อความ แนบรูปภาพ หรือกรอกข้อมูลขอรับใบอนุโมทนาบัตร']);
        exit;
    }

    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $max_bytes = 5 * 1024 * 1024; // 5 MB

    $image1_base64 = '';
    $image1_name   = '';
    $local_img1    = '';

    $image2_base64 = '';
    $image2_name   = '';
    $local_img2    = '';

    // Handle Image 1
    if ($has_img1) {
        $file = $_FILES['image1'];
        if ($file['size'] > $max_bytes) {
            echo json_encode(['success' => false, 'message' => 'รูปภาพที่ 1 มีขนาดเกิน 5 MB']);
            exit;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'รูปภาพที่ 1 ต้องเป็นไฟล์ JPG, PNG หรือ WebP เท่านั้น']);
            exit;
        }
        $new_name = 'photo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $upload_dir . $new_name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $local_img1 = 'uploads/' . $new_name;
            $file_data = file_get_contents($dest);
            $mime = mime_content_type($dest) ?: 'image/jpeg';
            $image1_base64 = 'data:' . $mime . ';base64,' . base64_encode($file_data);
            $image1_name   = $new_name;
        }
    }

    // Handle Image 2 (Slip)
    if ($has_img2) {
        $file = $_FILES['image2'];
        if ($file['size'] > $max_bytes) {
            echo json_encode(['success' => false, 'message' => 'รูปภาพที่ 2 (สลิปโอนเงิน) มีขนาดเกิน 5 MB']);
            exit;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'รูปภาพสลิปต้องเป็นไฟล์ JPG, PNG หรือ WebP เท่านั้น']);
            exit;
        }
        $new_name = 'slip_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $upload_dir . $new_name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $local_img2 = 'uploads/' . $new_name;
            $file_data = file_get_contents($dest);
            $mime = mime_content_type($dest) ?: 'image/jpeg';
            $image2_base64 = 'data:' . $mime . ';base64,' . base64_encode($file_data);
            $image2_name   = $new_name;
        }
    }

    // Prepare backup record
    $submission_id = 'CSH-' . date('Ymd-His') . '-' . rand(100, 999);
    $created_at    = date('Y-m-d H:i:s');

    $record = [
        'id'                => $submission_id,
        'created_at'        => $created_at,
        'donor_name'        => $donor_name ?: 'ผู้ไม่ประสงค์ออกนาม',
        'message'           => $message,
        'receipt_requested' => ($receipt_requested === '1') ? 'รับ' : 'ไม่รับ',
        'tax_name'          => $tax_name,
        'tax_id'            => $tax_id,
        'phone'             => $phone,
        'address'           => $address,
        'allow_social_post' => ($allow_social_post === '1') ? 'อนุญาต' : 'ไม่อนุญาต',
        'local_image1'      => $local_img1,
        'local_image2'      => $local_img2,
        'image1_base64'     => $image1_base64,
        'image1_name'       => $image1_name,
        'image2_base64'     => $image2_base64,
        'image2_name'       => $image2_name,
        'status'            => 'รอดำเนินการ',
        'synced_google'     => false
    ];

    // Local JSON Backup
    $backup_file = __DIR__ . '/../data/donations_backup.json';
    $backups = [];
    if (file_exists($backup_file)) {
        $raw = @file_get_contents($backup_file);
        if ($raw) {
            $backups = json_decode($raw, true) ?: [];
        }
    }
    
    // Save record without base64 to keep JSON backup file light
    $light_record = $record;
    unset($light_record['image1_base64'], $light_record['image2_base64']);
    $backups[] = $light_record;
    @file_put_contents($backup_file, json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Send to Google Sheets if configured
    $config = get_app_config();
    $google_url = trim($config['google_script_url'] ?? '');

    $google_result = null;
    $synced = false;
    $sync_error = null;

    if (!empty($google_url)) {
        $post_payload = json_encode([
            'donor_name'        => $record['donor_name'],
            'message'           => $record['message'],
            'receipt_requested' => $record['receipt_requested'],
            'tax_name'          => $record['tax_name'],
            'tax_id'            => $record['tax_id'],
            'phone'             => $record['phone'],
            'address'           => $record['address'],
            'allow_social_post' => $record['allow_social_post'],
            'image1_base64'     => $image1_base64,
            'image1_name'       => $image1_name,
            'image2_base64'     => $image2_base64,
            'image2_name'       => $image2_name
        ]);

        $ch = curl_init($google_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($curl_err) {
            $sync_error = 'Google Sheet Connection Error: ' . $curl_err;
        } else {
            $parsed = json_decode($response, true);
            if ($parsed && !empty($parsed['success'])) {
                $synced = true;
                $google_result = $parsed;
            } else {
                $sync_error = $parsed['message'] ?? 'Google Sheet script response error (HTTP ' . $http_code . ')';
            }
        }

        // Update synced status in backup
        if ($synced) {
            $backups[count($backups) - 1]['synced_google'] = true;
            @file_put_contents($backup_file, json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    if ($synced) {
        echo json_encode([
            'success'       => true,
            'message'       => 'โรงพยาบาลเชียงแสน ขอขอบพระคุณท่านอย่างยิ่ง',
            'synced_google' => true,
            'id'            => $submission_id
        ]);
    } elseif (!empty($google_url)) {
        echo json_encode([
            'success'       => true,
            'message'       => 'บันทึกข้อมูลในระบบแล้ว (ระบบบันทึกสำรองในเครื่องเรียบร้อย เนื่องจากพบปัญหาเชื่อมต่อ Google Sheet ชั่วคราว: ' . $sync_error . ')',
            'synced_google' => false,
            'warning'       => $sync_error,
            'id'            => $submission_id
        ]);
    } else {
        echo json_encode([
            'success'       => true,
            'message'       => 'บันทึกข้อมูลในระบบเรียบร้อยแล้ว (กำลังรอใส่ URL ของ Google Apps Script Web App เพื่อซิงก์เข้า Google Sheet อัตโนมัติ)',
            'synced_google' => false,
            'id'            => $submission_id
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการประมวลผล: ' . $e->getMessage()
    ]);
}
