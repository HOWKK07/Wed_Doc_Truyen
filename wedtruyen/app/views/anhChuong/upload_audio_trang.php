<?php
require_once '../../config/connect.php';

function createSubtitleFromAudio($audio_path) {
    $python = 'C:\\Users\\thinh\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
    $script = __DIR__ . '/../../api/generate_sub.py';
    $cmd = "$python \"$script\" \"$audio_path\"";
    exec($cmd . " 2>&1", $output, $return_var);

    error_log("CMD: $cmd");
    error_log("Output: " . print_r($output, true));
    error_log("Return: $return_var");

    $vtt_path = preg_replace('/\.\w+$/', '.vtt', $audio_path);
    if ($return_var === 0 && file_exists($vtt_path)) {
        // Trả về đường dẫn sub tương đối để lưu vào DB
        return "uploads/audio_trang/" . basename($vtt_path);
    }
    return null;
}

$id_anh = isset($_POST['id_anh']) ? (int)$_POST['id_anh'] : 0;
if (!$id_anh) {
    die("Lỗi: Không tìm thấy ID ảnh hợp lệ.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_anh = (int)$_POST['id_anh'];

    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('audio_') . '.' . $file_extension;
        // Đổi đường dẫn vật lý lưu file audio
        $target_dir = realpath(__DIR__ . '/../../../../uploads/audio_trang/') . '/';
        $file_path = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $file_path)) {
            $duong_dan_audio = "uploads/audio_trang/" . $file_name;
            $duong_dan_sub = createSubtitleFromAudio($file_path);

            // Lưu vào bảng audio_trang
            $sql = "INSERT INTO audio_trang (id_anh, duong_dan_audio, duong_dan_sub) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE duong_dan_audio = VALUES(duong_dan_audio), duong_dan_sub = VALUES(duong_dan_sub)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $id_anh, $duong_dan_audio, $duong_dan_sub);
            $stmt->execute();
        }
    }
    header("Location: ../anhChuong/list.php?id_chuong=$id_chuong");
    exit();
}
?>