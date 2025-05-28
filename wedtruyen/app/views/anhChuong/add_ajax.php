<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/anhChuongController.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Bạn cần đăng nhập.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lấy id_chuong từ GET parameter hoặc POST data
        $id_chuong = null;
        
        if (isset($_GET['id_chuong'])) {
            $id_chuong = (int)$_GET['id_chuong'];
        } elseif (isset($_POST['id_chuong'])) {
            $id_chuong = (int)$_POST['id_chuong'];
        }
        
        if (!$id_chuong) {
            echo json_encode(['success' => false, 'error' => 'Không tìm thấy ID chương.']);
            exit;
        }
        
        // Kiểm tra có file upload không
        if (!isset($_FILES['anh']) || !is_array($_FILES['anh']['name'])) {
            echo json_encode(['success' => false, 'error' => 'Không có ảnh nào được tải lên.']);
            exit;
        }
        
        // Xử lý upload ảnh
        $target_dir = __DIR__ . '/../../../../uploads/anhchuong/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Lấy số trang lớn nhất hiện tại
        require_once '../../models/anhChuongModel.php';
        $model = new AnhChuongModel($conn);
        $so_trang_lon_nhat = $model->laySoTrangLonNhat($id_chuong);
        
        $uploaded_count = 0;
        $errors = [];
        
        foreach ($_FILES['anh']['name'] as $index => $name) {
            if ($_FILES['anh']['error'][$index] !== UPLOAD_ERR_OK) {
                $errors[] = "Lỗi upload file: " . $name;
                continue;
            }
            
            $file_extension = pathinfo($name, PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                $errors[] = "File không hợp lệ: " . $name;
                continue;
            }
            
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['anh']['tmp_name'][$index], $file_path)) {
                $duong_dan_anh = "uploads/anhchuong/" . $file_name;
                $so_trang = $so_trang_lon_nhat + $uploaded_count + 1;
                
                if ($model->themAnh($id_chuong, $duong_dan_anh, $so_trang)) {
                    $uploaded_count++;
                } else {
                    $errors[] = "Không thể lưu vào database: " . $name;
                    // Xóa file đã upload nếu không lưu được vào DB
                    unlink($file_path);
                }
            } else {
                $errors[] = "Không thể upload file: " . $name;
            }
        }
        
        if ($uploaded_count > 0) {
            echo json_encode([
                'success' => true, 
                'message' => "Đã thêm $uploaded_count trang thành công",
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => 'Không thể thêm trang nào',
                'errors' => $errors
            ]);
        }
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Yêu cầu không hợp lệ']);
exit;
?>