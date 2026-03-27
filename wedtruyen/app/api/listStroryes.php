<?php
require_once '../config/connect.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

// Có thể thêm filter thể loại/loại truyện nếu muốn
$sql = "SELECT t.*, 
               (SELECT COUNT(*) FROM chuong c WHERE c.id_truyen = t.id_truyen) as max_chapter,
               (SELECT AVG(so_sao) FROM danh_gia WHERE id_truyen = t.id_truyen) as danh_gia,
               (SELECT trang_thai FROM truyen WHERE id_truyen = t.id_truyen) as trang_thai,
               (SELECT luot_xem FROM truyen WHERE id_truyen = t.id_truyen) as luot_xem
        FROM truyen t
        ORDER BY t.id_truyen DESC
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$stories = [];
while ($row = $result->fetch_assoc()) {
    $stories[] = $row;
}

echo json_encode([
    'success' => true,
    'stories' => $stories
]);