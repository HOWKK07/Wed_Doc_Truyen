<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Phương thức không hợp lệ']);
    exit;
}

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Bạn không có quyền thực hiện thao tác này']);
    exit;
}

// Lấy dữ liệu từ POST
$id_chuong = isset($_POST['id_chuong']) ? (int)$_POST['id_chuong'] : 0;
$so_chuong = isset($_POST['so_chuong']) ? (int)$_POST['so_chuong'] : 0;
$tieu_de = isset($_POST['tieu_de']) ? trim($_POST['tieu_de']) : '';
$id_truyen = isset($_POST['id_truyen']) ? (int)$_POST['id_truyen'] : 0;

// Validate dữ liệu
if ($id_chuong <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID chương không hợp lệ']);
    exit;
}

if ($so_chuong <= 0) {
    echo json_encode(['success' => false, 'error' => 'Số chương phải lớn hơn 0']);
    exit;
}

if (empty($tieu_de)) {
    echo json_encode(['success' => false, 'error' => 'Tiêu đề không được để trống']);
    exit;
}

try {
    // Kiểm tra chapter có tồn tại không
    $check_sql = "SELECT id_chuong, id_truyen FROM chuong WHERE id_chuong = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id_chuong);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        echo json_encode(['success' => false, 'error' => 'Chương không tồn tại']);
        exit;
    }
    
    $chapter_data = $check_result->fetch_assoc();
    $id_truyen_db = $chapter_data['id_truyen'];
    
    // Kiểm tra số chương có bị trùng không (trừ chính nó)
    $duplicate_sql = "SELECT id_chuong FROM chuong WHERE id_truyen = ? AND so_chuong = ? AND id_chuong != ?";
    $duplicate_stmt = $conn->prepare($duplicate_sql);
    $duplicate_stmt->bind_param("iii", $id_truyen_db, $so_chuong, $id_chuong);
    $duplicate_stmt->execute();
    $duplicate_result = $duplicate_stmt->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Số chương ' . $so_chuong . ' đã tồn tại']);
        exit;
    }
    
    // Cập nhật chapter
    $update_sql = "UPDATE chuong SET so_chuong = ?, tieu_de = ? WHERE id_chuong = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("isi", $so_chuong, $tieu_de, $id_chuong);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cập nhật chương thành công',
            'data' => [
                'id_chuong' => $id_chuong,
                'so_chuong' => $so_chuong,
                'tieu_de' => $tieu_de
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể cập nhật chương: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Lỗi: ' . $e->getMessage()]);
}

exit;
?>