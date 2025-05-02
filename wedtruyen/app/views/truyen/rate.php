<?php
session_start();
require_once '../../config/connect.php';

if (!isset($_SESSION['user'])) {
    die("Bạn cần đăng nhập để đánh giá.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_truyen = $_POST['id_truyen'];
    $id_nguoidung = $_SESSION['user']['id_nguoidung'];
    $so_sao = (int)$_POST['so_sao'];

    // Kiểm tra giá trị so_sao
    if ($so_sao < 1 || $so_sao > 5) {
        die("Giá trị đánh giá không hợp lệ. Vui lòng chọn từ 1 đến 5 sao.");
    }

    // Kiểm tra nếu người dùng đã đánh giá truyện này
    $sql_check = "SELECT * FROM ratings WHERE id_truyen = ? AND id_nguoidung = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_truyen, $id_nguoidung);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Cập nhật đánh giá nếu đã tồn tại
        $sql_update = "UPDATE ratings SET so_sao = ? WHERE id_truyen = ? AND id_nguoidung = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $so_sao, $id_truyen, $id_nguoidung);
        $stmt_update->execute();
    } else {
        // Thêm đánh giá mới
        $sql_insert = "INSERT INTO ratings (id_truyen, id_nguoidung, so_sao) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iii", $id_truyen, $id_nguoidung, $so_sao);
        $stmt_insert->execute();
    }

    header("Location: chiTietTruyen.php?id_truyen=$id_truyen");
    exit();
}
?>