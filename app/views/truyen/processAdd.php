<?php
require_once '../../controllers/truyenController.php';

$controller = new TruyenController($conn);
$controller->themTruyen();
?>