<?php
session_start();
if (isset($_SESSION['user'])) {
    session_destroy();
}
header("Location: /Wed_Doc_Truyen/wedtruyen/index.php");
exit();
?>