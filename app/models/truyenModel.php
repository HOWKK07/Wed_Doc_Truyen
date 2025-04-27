<?php
class TruyenModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function themTruyen($ten_truyen, $tac_gia, $the_loai, $loai_truyen, $anh_bia, $mo_ta) {
        $sql = "INSERT INTO truyen (ten_truyen, tac_gia, the_loai, loai_truyen, anh_bia, mo_ta)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $ten_truyen, $tac_gia, $the_loai, $loai_truyen, $anh_bia, $mo_ta);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
?>