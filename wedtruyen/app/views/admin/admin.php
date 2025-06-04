<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../controllers/truyenController.php';
require_once __DIR__ . '/../../models/truyenModel.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
    header("Location: ../taiKhoan/login.php");
    exit();
}

// Xử lý các yêu cầu AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    switch ($_GET['action']) {
        case 'getTruyen':
            $sql = "SELECT t.*, lt.ten_loai_truyen 
                    FROM truyen t 
                    LEFT JOIN loai_truyen lt ON t.id_loai_truyen = lt.id_loai_truyen 
                    ORDER BY t.ngay_tao DESC";
            $result = $conn->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();
            
        case 'getTheLoai':
            $sql = "SELECT * FROM theloai ORDER BY ngay_tao DESC";
            $result = $conn->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();
            
        case 'getLoaiTruyen':
            $sql = "SELECT * FROM loai_truyen ORDER BY ngay_tao DESC";
            $result = $conn->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();
            
        case 'getTaiKhoan':
            $sql = "SELECT id_nguoidung, ten_dang_nhap, email, vai_tro, ngay_tao 
                    FROM nguoidung ORDER BY ngay_tao DESC";
            $result = $conn->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();
            
        case 'getStats':
            $stats = [];
            
            $result = $conn->query("SELECT COUNT(*) as count FROM nguoidung");
            $stats['accounts'] = $result->fetch_assoc()['count'];
            
            $result = $conn->query("SELECT COUNT(*) as count FROM truyen");
            $stats['stories'] = $result->fetch_assoc()['count'];
            
            $result = $conn->query("SELECT COUNT(*) as count FROM theloai");
            $stats['genres'] = $result->fetch_assoc()['count'];
            
            $result = $conn->query("SELECT COUNT(*) as count FROM loai_truyen");
            $stats['types'] = $result->fetch_assoc()['count'];
            
            echo json_encode($stats);
            exit();
            
        case 'getTruyenDetail':
            $id = (int)$_GET['id'];
            $sql = "SELECT * FROM truyen WHERE id_truyen = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $truyen = $result->fetch_assoc();
            
            $sql_theloai = "SELECT id_theloai FROM truyen_theloai WHERE id_truyen = ?";
            $stmt_theloai = $conn->prepare($sql_theloai);
            $stmt_theloai->bind_param("i", $id);
            $stmt_theloai->execute();
            $result_theloai = $stmt_theloai->get_result();
            
            $theloai_selected = [];
            while ($row = $result_theloai->fetch_assoc()) {
                $theloai_selected[] = $row['id_theloai'];
            }
            
            $truyen['theloai_selected'] = $theloai_selected;
            echo json_encode($truyen);
            exit();
            
        case 'deleteTruyen':
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                
                $stmt = $conn->prepare("SELECT anh_bia FROM truyen WHERE id_truyen = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $truyen = $result->fetch_assoc();
                
                $stmt = $conn->prepare("DELETE FROM truyen WHERE id_truyen = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    if ($truyen && $truyen['anh_bia'] && file_exists("../../../" . $truyen['anh_bia'])) {
                        unlink("../../../" . $truyen['anh_bia']);
                    }
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Không thể xóa truyện']);
                }
            }
            exit();
            
        case 'deleteTheLoai':
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $stmt = $conn->prepare("DELETE FROM theloai WHERE id_theloai = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Không thể xóa thể loại']);
                }
            }
            exit();
            
        case 'deleteLoaiTruyen':
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $stmt = $conn->prepare("DELETE FROM loai_truyen WHERE id_loai_truyen = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Không thể xóa loại truyện']);
                }
            }
            exit();
            
        case 'deleteTaiKhoan':
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                
                $check_sql = "SELECT COUNT(*) as total FROM nguoidung WHERE vai_tro = 'admin'";
                $result = $conn->query($check_sql);
                $row = $result->fetch_assoc();
                
                if ($row['total'] <= 1) {
                    $check_user = "SELECT vai_tro FROM nguoidung WHERE id_nguoidung = ?";
                    $stmt = $conn->prepare($check_user);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    
                    if ($user && $user['vai_tro'] === 'admin') {
                        echo json_encode(['success' => false, 'error' => 'Không thể xóa tài khoản admin cuối cùng']);
                        exit();
                    }
                }
                
                $stmt = $conn->prepare("DELETE FROM nguoidung WHERE id_nguoidung = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Không thể xóa tài khoản']);
                }
            }
            exit();
            
        case 'getTaiKhoanDetail':
            $id = (int)$_GET['id'];
            $sql = "SELECT id_nguoidung, ten_dang_nhap, email, vai_tro FROM nguoidung WHERE id_nguoidung = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc());
            exit();
    }
}

// Xử lý POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'addTruyen':
            try {
                $ten_truyen = $_POST['ten_truyen'];
                $tac_gia = $_POST['tac_gia'];
                $id_loai_truyen = (int)$_POST['id_loai_truyen'];
                $trang_thai = $_POST['trang_thai'];
                $nam_phat_hanh = (int)$_POST['nam_phat_hanh'];
                $mo_ta = $_POST['mo_ta'];
                $the_loai = isset($_POST['the_loai']) ? $_POST['the_loai'] : [];
                // Xử lý ảnh bìa
                $anh_bia = '';
                if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['error'] === UPLOAD_ERR_OK) {
                    // Đường dẫn tuyệt đối đến thư mục uploads/anhbia
                    $target_dir = __DIR__ . '/../../../../uploads/anhbia/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    $file_name = uniqid() . '_' . basename($_FILES["anh_bia"]["name"]);
                    $target_file = $target_dir . $file_name;
                    if (move_uploaded_file($_FILES["anh_bia"]["tmp_name"], $target_file)) {
                        $anh_bia = "uploads/anhbia/" . $file_name; // Đường dẫn lưu vào DB
                    }
                }
                $truyenModel = new TruyenModel($conn);
                $id_truyen = $truyenModel->themTruyen($ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh);
                // Thêm thể loại cho truyện
                foreach ($the_loai as $id_theloai) {
                    $truyenModel->themTheLoaiChoTruyen($id_truyen, $id_theloai);
                }
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit();

        case 'addTheLoai':
            try {
                $ten_theloai = $_POST['ten_theloai'];
                require_once __DIR__ . '/../../models/theLoaiModel.php';
                $theLoaiModel = new TheLoaiModel($conn);
                $theLoaiModel->themTheLoai($ten_theloai);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit();

        case 'addLoaiTruyen':
            try {
                $ten_loai_truyen = $_POST['ten_loai_truyen'];
                require_once __DIR__ . '/../../models/loaiTruyenModel.php';
                $loaiTruyenModel = new LoaiTruyenModel($conn);
                $loaiTruyenModel->themLoaiTruyen($ten_loai_truyen);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit();

        case 'updateTruyen':
            try {
                $id_truyen = (int)$_POST['id_truyen'];
                $ten_truyen = $_POST['ten_truyen'];
                $tac_gia = $_POST['tac_gia'];
                $id_loai_truyen = (int)$_POST['id_loai_truyen'];
                $trang_thai = $_POST['trang_thai'];
                $nam_phat_hanh = (int)$_POST['nam_phat_hanh'];
                $mo_ta = $_POST['mo_ta'];
                $the_loai = isset($_POST['the_loai']) ? $_POST['the_loai'] : [];

                // Lấy ảnh bìa cũ từ DB
                $stmt = $conn->prepare("SELECT anh_bia FROM truyen WHERE id_truyen = ?");
                $stmt->bind_param("i", $id_truyen);
                $stmt->execute();
                $result = $stmt->get_result();
                $truyen = $result->fetch_assoc();
                $anh_bia = $truyen['anh_bia'];

                // Nếu có upload ảnh mới thì xử lý
                if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['error'] === UPLOAD_ERR_OK) {
                    // Đường dẫn tuyệt đối đến thư mục uploads/anhbia
                    $target_dir = __DIR__ . '/../../../../uploads/anhbia/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    $file_name = uniqid() . '_' . basename($_FILES["anh_bia"]["name"]);
                    $target_file = $target_dir . $file_name;
                    if (move_uploaded_file($_FILES["anh_bia"]["tmp_name"], $target_file)) {
                        $anh_bia = "uploads/anhbia/" . $file_name;
                    }
                }

                $truyenModel = new TruyenModel($conn);
                $truyenModel->capNhatTruyen($id_truyen, $ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh);

                // Cập nhật thể loại
                $truyenModel->xoaTheLoaiTruyen($id_truyen);
                foreach ($the_loai as $id_theloai) {
                    $truyenModel->themTheLoaiChoTruyen($id_truyen, $id_theloai);
                }
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit();

        case 'updateTheLoai':
            try {
                $id_theloai = (int)$_POST['id_theloai'];
                $ten_theloai = $_POST['ten_theloai'];
                require_once __DIR__ . '/../../models/theLoaiModel.php';
                $theLoaiModel = new TheLoaiModel($conn);
                $theLoaiModel->capNhatTheLoai($id_theloai, $ten_theloai);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit();

        case 'updateLoaiTruyen':
            try {
                $id_loai_truyen = (int)$_POST['id_loai_truyen'];
                $ten_loai_truyen = $_POST['ten_loai_truyen'];
                require_once __DIR__ . '/../../models/loaiTruyenModel.php';
                $loaiTruyenModel = new LoaiTruyenModel($conn);
                $loaiTruyenModel->capNhatLoaiTruyen($id_loai_truyen, $ten_loai_truyen);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit();
        // ...các case khác...
        default:
            echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ']);
            exit();
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Quản lý truyện</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Additional CSS for better modal display */
        .checkbox-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .checkbox-list label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
            padding: 5px;
            border-radius: 3px;
            transition: background-color 0.2s;
        }
        
        .checkbox-list label:hover {
            background-color: #e9ecef;
        }
        
        .checkbox-list input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
        }
        
        #currentImage {
            text-align: center;
            margin-top: 10px;
        }
        
        #currentImage img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        #currentImage p {
            margin-top: 5px;
            color: #666;
            font-size: 14px;
        }
        
        /* Loading overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 2% auto;
                max-height: 95vh;
            }
            
            .checkbox-list {
                max-height: 100px;
            }
        }
    </style>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/admin/dashboard.css">
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-logo">
            <h1>Admin Panel</h1>
            <span class="badge">PRO</span>
        </div>
        <div class="admin-user">
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['ten_dang_nhap']); ?>!</span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user']['ten_dang_nhap']); ?>&background=007bff&color=fff" alt="Admin">
        </div>
    </div>
    
    <!-- Admin Container -->
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-section">DASHBOARD</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="#" onclick="showDashboard()">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tổng quan</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-section">QUẢN LÝ</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="#" onclick="showTaiKhoan()">
                        <i class="fas fa-users"></i>
                        <span>Quản lý tài khoản</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showTruyen()" class="active">
                        <i class="fas fa-book"></i>
                        <span>Quản lý truyện</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showTheLoai()">
                        <i class="fas fa-tags"></i>
                        <span>Quản lý thể loại</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showLoaiTruyen()">
                        <i class="fas fa-layer-group"></i>
                        <span>Quản lý loại truyện</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-section">CÔNG CỤ</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="#" onclick="showThongKe()">
                        <i class="fas fa-chart-bar"></i>
                        <span>Thống kê</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showCaiDat()">
                        <i class="fas fa-cog"></i>
                        <span>Cài đặt</span>
                    </a>
                </li>
                <li>
                    <a href="/Wed_Doc_Truyen/wedtruyen/index.php">
                        <i class="fas fa-home"></i>
                        <span>Về trang chủ</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Dashboard View -->
            <div id="dashboardView" style="display: none;">
                <div class="stats-cards" id="statsCards">
                    <!-- Stats will be loaded here -->
                </div>
                
                <div class="content-panel">
                    <div class="content-header">
                        <h2>Hoạt động gần đây</h2>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Hoạt động</th>
                                <th>Chi tiết</th>
                                <th>Người dùng</th>
                            </tr>
                        </thead>
                        <tbody id="activityLog">
                            <!-- Activity log will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quản lý Tài khoản View -->
            <div id="taiKhoanView" style="display: none;">
                <div class="content-panel">
                    <div class="content-header">
                        <h2>Quản lý tài khoản</h2>
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="taiKhoanTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quản lý Truyện View -->
            <div id="truyenView">
                <div class="content-panel">
                    <div class="content-header">
                        <h2>Quản lý truyện</h2>
                        <button class="add-btn" onclick="openAddTruyenModal()">
                            <i class="fas fa-plus"></i> Thêm truyện mới
                        </button>
                        <input type="text" id="searchTruyenInput" placeholder="Tìm kiếm truyện..." style="margin-left:20px; padding:5px 10px; border-radius:4px; border:1px solid #ccc; width:250px;">
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên truyện</th>
                                <th>Tác giả</th>
                                <th>Loại truyện</th>
                                <th>Trạng thái</th>
                                <th>Năm phát hành</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="truyenTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quản lý Thể loại View -->
            <div id="theLoaiView" style="display: none;">
                <div class="content-panel">
                    <div class="content-header">
                        <h2>Quản lý thể loại</h2>
                        <button class="add-btn" onclick="openAddTheLoaiModal()">
                            <i class="fas fa-plus"></i> Thêm thể loại mới
                        </button>
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên thể loại</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="theLoaiTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quản lý Loại truyện View -->
            <div id="loaiTruyenView" style="display: none;">
                <div class="content-panel">
                    <div class="content-header">
                        <h2>Quản lý loại truyện</h2>
                        <button class="add-btn" onclick="openAddLoaiTruyenModal()">
                            <i class="fas fa-plus"></i> Thêm loại truyện mới
                        </button>
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên loại truyện</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="loaiTruyenTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Truyện Modal -->
    <div id="truyenModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="truyenModalTitle">Thêm truyện mới</h3>
                <span class="close-btn" onclick="closeTruyenModal()">&times;</span>
            </div>
            <form id="truyenForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="truyenAction" value="addTruyen">
                <input type="hidden" name="id_truyen" id="id_truyen">
                
                <div class="form-group">
                    <label>Tên truyện</label>
                    <input type="text" name="ten_truyen" id="ten_truyen" required>
                </div>
                
                <div class="form-group">
                    <label>Tác giả</label>
                    <input type="text" name="tac_gia" id="tac_gia" required>
                </div>
                
                <div class="form-group">
                    <label>Loại truyện</label>
                    <select name="id_loai_truyen" id="id_loai_truyen" required>
                        <option value="">Chọn loại truyện</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Thể loại</label>
                    <div class="checkbox-list" id="theLoaiList">
                        <!-- Checkboxes will be loaded here -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="trang_thai" required>
                        <option value="Đang ra">Đang ra</option>
                        <option value="Hoàn thành">Hoàn thành</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Năm phát hành</label>
                    <input type="number" name="nam_phat_hanh" id="nam_phat_hanh" min="1900" max="<?php echo date('Y'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Ảnh bìa</label>
                    <input type="file" name="anh_bia" id="anh_bia" accept="image/*">
                    <div id="currentImage" style="margin-top: 10px;"></div>
                </div>
                
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="mo_ta" id="mo_ta" rows="4" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeTruyenModal()">Hủy</button>
                    <button type="submit" class="btn-save">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add/Edit Thể loại Modal -->
    <div id="theLoaiModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 id="theLoaiModalTitle">Thêm thể loại mới</h3>
                <span class="close-btn" onclick="closeTheLoaiModal()">&times;</span>
            </div>
            <form id="theLoaiForm">
                <input type="hidden" name="action" id="theLoaiAction" value="addTheLoai">
                <input type="hidden" name="id_theloai" id="id_theloai">
                
                <div class="form-group">
                    <label>Tên thể loại</label>
                    <input type="text" name="ten_theloai" id="ten_theloai" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeTheLoaiModal()">Hủy</button>
                    <button type="submit" class="btn-save">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add/Edit Loại truyện Modal -->
    <div id="loaiTruyenModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 id="loaiTruyenModalTitle">Thêm loại truyện mới</h3>
                <span class="close-btn" onclick="closeLoaiTruyenModal()">&times;</span>
            </div>
            <form id="loaiTruyenForm">
                <input type="hidden" name="action" id="loaiTruyenAction" value="addLoaiTruyen">
                <input type="hidden" name="id_loai_truyen" id="id_loai_truyen_modal">
                
                <div class="form-group">
                    <label>Tên loại truyện</label>
                    <input type="text" name="ten_loai_truyen" id="ten_loai_truyen" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeLoaiTruyenModal()">Hủy</button>
                    <button type="submit" class="btn-save">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Tài khoản Modal -->
    <div id="taiKhoanModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 id="taiKhoanModalTitle">Sửa tài khoản</h3>
                <span class="close-btn" onclick="closeTaiKhoanModal()">&times;</span>
            </div>
            <form id="taiKhoanForm">
                <input type="hidden" name="action" value="editTaiKhoan">
                <input type="hidden" name="id_nguoidung" id="id_nguoidung">
                
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="ten_dang_nhap" id="ten_dang_nhap_modal" readonly>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email_modal" required>
                </div>
                
                <div class="form-group">
                    <label>Vai trò</label>
                    <select name="vai_tro" id="vai_tro_modal" required>
                        <option value="nguoidung">Người dùng</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeTaiKhoanModal()">Hủy</button>
                    <button type="submit" class="btn-save">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <script src="/Wed_Doc_Truyen/wedtruyen/assets/js/admin/dashboard.js"></script>
</body>
</html>