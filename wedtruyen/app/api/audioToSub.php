<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Chỉ hỗ trợ POST']);
    exit;
}

if (!isset($_FILES['audio'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu file audio']);
    exit;
}

// Lưu file tạm
$tmp_name = $_FILES['audio']['tmp_name'];
$ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
$audio_name = uniqid('audio_') . '.' . $ext;
$target_dir = __DIR__ . '/../../../uploads/audio_trang/';
if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
$audio_path = $target_dir . $audio_name;
move_uploaded_file($tmp_name, $audio_path);

// Gọi script Python để tạo sub
$python = 'python'; // hoặc 'python3' tùy hệ thống
$script = __DIR__ . '/generate_sub.py';
$cmd = "$python \"$script\" \"$audio_path\"";
exec($cmd, $output, $return_var);

$vtt_name = str_replace('.' . $ext, '.vtt', $audio_name);
$vtt_path = $target_dir . $vtt_name;

if ($return_var === 0 && file_exists($vtt_path)) {
    echo json_encode([
        'success' => true,
        'audio_url' => "uploads/audio_trang/$audio_name",
        'sub_url' => "uploads/audio_trang/$vtt_name"
    ]);
} else {
    echo json_encode(['error' => 'Không thể tạo phụ đề tự động.']);
}