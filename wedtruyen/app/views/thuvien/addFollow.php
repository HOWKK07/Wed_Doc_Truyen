<?php
session_start();
require_once '../../config/connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thêm vào thư viện.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

if (!isset($_SESSION['user']['id_nguoidung'])) {
    echo json_encode(['success' => false, 'message' => 'Thông tin người dùng không hợp lệ.']);
    exit();
}

$id_nguoidung = $_SESSION['user']['id_nguoidung'];
$id_truyen = $data['id_truyen'] ?? null;

if (!$id_truyen) {
    echo json_encode(['success' => false, 'message' => 'ID truyện không hợp lệ.']);
    exit();
}

// Check if connection was successful
if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode(['success' => false, 'message' => 'Kết nối cơ sở dữ liệu không hợp lệ.']);
    exit();
}

// Kiểm tra xem truyện đã được thêm vào thư viện chưa
$sql_check = "SELECT * FROM follows WHERE id_nguoidung = ? AND id_truyen = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn cơ sở dữ liệu.']);
    exit();
}

$stmt_check->bind_param("ii", $id_nguoidung, $id_truyen);
if (!$stmt_check->execute()) {
    echo json_encode(['success' => false, 'message' => 'Lỗi thực thi truy vấn.']);
    exit();
}

$result_check = $stmt_check->get_result();
if (!$result_check) {
    echo json_encode(['success' => false, 'message' => 'Lỗi lấy kết quả truy vấn.']);
    exit();
}

if ($result_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Truyện đã có trong thư viện.']);
    exit();
}

// Thêm truyện vào thư viện
$sql_insert = "INSERT INTO follows (id_nguoidung, id_truyen) VALUES (?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn thêm vào thư viện.']);
    exit();
}

$stmt_insert->bind_param("ii", $id_nguoidung, $id_truyen);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể thêm truyện vào thư viện.']);
}

// Close statements
if (isset($stmt_check)) $stmt_check->close();
if (isset($stmt_insert)) $stmt_insert->close();
?>