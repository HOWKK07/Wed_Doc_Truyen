<?php
require_once '../../config/connect.php';
require_once '../../models/truyenModel.php';

class TruyenController {
    private $model;

    public function __construct($conn) {
        $this->model = new TruyenModel($conn);
    }

    public function themTruyen() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_truyen = $_POST['ten_truyen'];
            $tac_gia = $_POST['tac_gia'];
            $the_loai = $_POST['the_loai'];
            $loai_truyen = $_POST['loai_truyen'];
            $mo_ta = $_POST['mo_ta'];

            // Xử lý upload ảnh bìa
            $target_dir = "../../uploads/";
            $anh_bia = $target_dir . basename($_FILES["anh_bia"]["name"]);
            move_uploaded_file($_FILES["anh_bia"]["tmp_name"], $anh_bia);

            // Gọi model để thêm truyện
            $result = $this->model->themTruyen($ten_truyen, $tac_gia, $the_loai, $loai_truyen, $anh_bia, $mo_ta);

            if ($result) {
                header("Location: ../../index.php?success=1");
                exit();
            } else {
                echo "Lỗi: Không thể thêm truyện.";
            }
        }
    }
}
?>