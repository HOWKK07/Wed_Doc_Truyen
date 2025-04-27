<?php
require_once '../../controllers/taiKhoanController.php';

$controller = new TaiKhoanController($conn);
$controller->dangKy();
?>