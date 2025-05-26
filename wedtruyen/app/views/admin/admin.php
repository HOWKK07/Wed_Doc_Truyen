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
    
    <script>
// Admin Dashboard JavaScript

// Navigation functions
function showDashboard() {
    hideAllViews();
    document.getElementById('dashboardView').style.display = 'block';
    setActiveMenu(0);
    loadStats();
    loadActivityLog();
}

function showTaiKhoan() {
    hideAllViews();
    document.getElementById('taiKhoanView').style.display = 'block';
    setActiveMenu(1);
    loadTaiKhoanData();
}

function showTruyen() {
    hideAllViews();
    document.getElementById('truyenView').style.display = 'block';
    setActiveMenu(2);
    loadTruyenData();
}

function showTheLoai() {
    hideAllViews();
    document.getElementById('theLoaiView').style.display = 'block';
    setActiveMenu(3);
    loadTheLoaiData();
}

function showLoaiTruyen() {
    hideAllViews();
    document.getElementById('loaiTruyenView').style.display = 'block';
    setActiveMenu(4);
    loadLoaiTruyenData();
}

function showThongKe() {
    alert('Chức năng thống kê đang được phát triển');
}

function showCaiDat() {
    alert('Chức năng cài đặt đang được phát triển');
}

function hideAllViews() {
    const views = ['dashboardView', 'taiKhoanView', 'truyenView', 'theLoaiView', 'loaiTruyenView'];
    views.forEach(view => {
        const element = document.getElementById(view);
        if (element) element.style.display = 'none';
    });
}

function setActiveMenu(index) {
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    menuItems.forEach(item => item.classList.remove('active'));
    if (menuItems[index]) {
        menuItems[index].classList.add('active');
    }
}

// Load data functions
async function loadStats() {
    try {
        const response = await fetch('admin.php?action=getStats');
        const stats = await response.json();
        
        document.getElementById('statsCards').innerHTML = `
            <div class="stat-card accounts">
                <div class="stat-info">
                    <h3>${stats.accounts}</h3>
                    <p>Tài khoản</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            
            <div class="stat-card stories">
                <div class="stat-info">
                    <h3>${stats.stories}</h3>
                    <p>Truyện</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            
            <div class="stat-card chapters">
                <div class="stat-info">
                    <h3>${stats.genres}</h3>
                    <p>Thể loại</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            
            <div class="stat-card lost-stories">
                <div class="stat-info">
                    <h3>${stats.types}</h3>
                    <p>Loại truyện</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadActivityLog() {
    // Simulate activity log - trong thực tế sẽ lấy từ database
    const activities = [
        { time: '10 phút trước', action: 'Thêm truyện mới', detail: 'One Piece Chapter 1100', user: 'Admin' },
        { time: '1 giờ trước', action: 'Cập nhật thể loại', detail: 'Thêm thể loại "Isekai"', user: 'Admin' },
        { time: '2 giờ trước', action: 'Xóa tài khoản', detail: 'Xóa user123', user: 'Admin' }
    ];
    
    const tbody = document.getElementById('activityLog');
    tbody.innerHTML = activities.map(activity => `
        <tr>
            <td>${activity.time}</td>
            <td>${activity.action}</td>
            <td>${activity.detail}</td>
            <td>${activity.user}</td>
        </tr>
    `).join('');
}

// Load Tài khoản data
async function loadTaiKhoanData() {
    try {
        const response = await fetch('admin.php?action=getTaiKhoan');
        const data = await response.json();
        
        const tbody = document.getElementById('taiKhoanTableBody');
        tbody.innerHTML = '';
        
        data.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.id_nguoidung}</td>
                <td>${escapeHtml(user.ten_dang_nhap)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td><span class="status-badge ${user.vai_tro === 'admin' ? 'completed' : 'ongoing'}">${user.vai_tro}</span></td>
                <td>${formatDate(user.ngay_tao)}</td>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="editTaiKhoan(${user.id_nguoidung})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn-delete" onclick="deleteTaiKhoan(${user.id_nguoidung})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading tai khoan data:', error);
    }
}

let allTruyenData = []; // Lưu toàn bộ dữ liệu để lọc

async function loadTruyenData() {
    try {
        const response = await fetch('admin.php?action=getTruyen');
        const data = await response.json();
        allTruyenData = data; // Lưu lại để dùng cho tìm kiếm
        renderTruyenTable(data);
    } catch (error) {
        console.error('Error loading truyen data:', error);
    }
}

function renderTruyenTable(data) {
    const tbody = document.getElementById('truyenTableBody');
    tbody.innerHTML = '';
    data.forEach(truyen => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${truyen.id_truyen}</td>
            <td>${escapeHtml(truyen.ten_truyen)}</td>
            <td>${escapeHtml(truyen.tac_gia)}</td>
            <td>${escapeHtml(truyen.ten_loai_truyen || '')}</td>
            <td><span class="status-badge ${truyen.trang_thai === 'Hoàn thành' ? 'completed' : 'ongoing'}">${truyen.trang_thai}</span></td>
            <td>${truyen.nam_phat_hanh}</td>
            <td>${formatDate(truyen.ngay_tao)}</td>
            <td class="action-buttons">
                <button class="btn-edit" onclick="event.stopPropagation(); editTruyen(${truyen.id_truyen})">
                    <i class="fas fa-edit"></i> Sửa
                </button>
                <button class="btn-delete" onclick="event.stopPropagation(); deleteTruyen(${truyen.id_truyen})">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </td>
        `;
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (e.target.closest('.action-buttons')) return;
            window.location.href = '../truyen/chiTietTruyen.php?id_truyen=' + truyen.id_truyen;
        });
        tbody.appendChild(row);
    });
}

// Xử lý tìm kiếm
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...
    document.getElementById('searchTruyenInput').addEventListener('input', function() {
        const keyword = this.value.trim().toLowerCase();
        const filtered = allTruyenData.filter(truyen =>
            Object.values(truyen).some(val =>
                (val + '').toLowerCase().includes(keyword)
            )
        );
        renderTruyenTable(filtered);
    });
});

async function loadTheLoaiData() {
    try {
        const response = await fetch('admin.php?action=getTheLoai');
        const data = await response.json();
        
        const tbody = document.getElementById('theLoaiTableBody');
        tbody.innerHTML = '';
        
        data.forEach(theloai => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${theloai.id_theloai}</td>
                <td>${escapeHtml(theloai.ten_theloai)}</td>
                <td>${formatDate(theloai.ngay_tao)}</td>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="editTheLoai(${theloai.id_theloai})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn-delete" onclick="deleteTheLoai(${theloai.id_theloai})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading the loai data:', error);
    }
}

async function loadLoaiTruyenData() {
    try {
        const response = await fetch('admin.php?action=getLoaiTruyen');
        const data = await response.json();
        
        const tbody = document.getElementById('loaiTruyenTableBody');
        tbody.innerHTML = '';
        
        data.forEach(loaitruyen => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${loaitruyen.id_loai_truyen}</td>
                <td>${escapeHtml(loaitruyen.ten_loai_truyen)}</td>
                <td>${formatDate(loaitruyen.ngay_tao)}</td>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="editLoaiTruyen(${loaitruyen.id_loai_truyen})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn-delete" onclick="deleteLoaiTruyen(${loaitruyen.id_loai_truyen})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading loai truyen data:', error);
    }
}

// Modal functions for Truyện
function openAddTruyenModal() {
    document.getElementById('truyenModalTitle').textContent = 'Thêm truyện mới';
    document.getElementById('truyenAction').value = 'addTruyen';
    document.getElementById('truyenForm').reset();
    document.getElementById('id_truyen').value = '';
    document.getElementById('currentImage').innerHTML = '';
    document.getElementById('truyenModal').style.display = 'block';
    loadLoaiTruyenOptions();
    loadTheLoaiCheckboxes();
}

function closeTruyenModal() {
    document.getElementById('truyenModal').style.display = 'none';
}

async function editTruyen(id) {
    try {
        const response = await fetch(`admin.php?action=getTruyenDetail&id=${id}`);
        const truyen = await response.json();
        
        document.getElementById('truyenModalTitle').textContent = 'Sửa truyện';
        document.getElementById('truyenAction').value = 'updateTruyen';
        document.getElementById('id_truyen').value = truyen.id_truyen;
        document.getElementById('ten_truyen').value = truyen.ten_truyen;
        document.getElementById('tac_gia').value = truyen.tac_gia;
        document.getElementById('trang_thai').value = truyen.trang_thai;
        document.getElementById('nam_phat_hanh').value = truyen.nam_phat_hanh;
        document.getElementById('mo_ta').value = truyen.mo_ta;
        
        // Hiển thị ảnh hiện tại
        if (truyen.anh_bia) {
            document.getElementById('currentImage').innerHTML = `
                <img src="/Wed_Doc_Truyen/${truyen.anh_bia}" style="max-width: 200px; max-height: 200px;">
                <p>Ảnh hiện tại</p>
            `;
        }
        
        await loadLoaiTruyenOptions();
        document.getElementById('id_loai_truyen').value = truyen.id_loai_truyen;
        
        await loadTheLoaiCheckboxes();
        // Check các thể loại đã chọn
        if (truyen.theloai_selected) {
            truyen.theloai_selected.forEach(id => {
                const checkbox = document.querySelector(`input[name="the_loai[]"][value="${id}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        document.getElementById('truyenModal').style.display = 'block';
    } catch (error) {
        console.error('Error loading truyen detail:', error);
        alert('Có lỗi xảy ra khi tải thông tin truyện');
    }
}

async function deleteTruyen(id) {
    if (confirm('Bạn có chắc chắn muốn xóa truyện này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteTruyen&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa truyện thành công!');
                loadTruyenData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa truyện'));
            }
        } catch (error) {
            console.error('Error deleting truyen:', error);
            alert('Có lỗi xảy ra khi xóa truyện');
        }
    }
}

// Modal functions for Thể loại
function openAddTheLoaiModal() {
    document.getElementById('theLoaiModalTitle').textContent = 'Thêm thể loại mới';
    document.getElementById('theLoaiAction').value = 'addTheLoai';
    document.getElementById('theLoaiForm').reset();
    document.getElementById('id_theloai').value = '';
    document.getElementById('theLoaiModal').style.display = 'block';
}

function closeTheLoaiModal() {
    document.getElementById('theLoaiModal').style.display = 'none';
}

async function editTheLoai(id) {
    const response = await fetch('admin.php?action=getTheLoai');
    const data = await response.json();
    const theloai = data.find(tl => tl.id_theloai == id);
    
    if (theloai) {
        document.getElementById('theLoaiModalTitle').textContent = 'Sửa thể loại';
        document.getElementById('theLoaiAction').value = 'updateTheLoai';
        document.getElementById('id_theloai').value = theloai.id_theloai;
        document.getElementById('ten_theloai').value = theloai.ten_theloai;
        document.getElementById('theLoaiModal').style.display = 'block';
    }
}

async function deleteTheLoai(id) {
    if (confirm('Bạn có chắc chắn muốn xóa thể loại này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteTheLoai&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa thể loại thành công!');
                loadTheLoaiData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa thể loại'));
            }
        } catch (error) {
            console.error('Error deleting the loai:', error);
            alert('Có lỗi xảy ra khi xóa thể loại');
        }
    }
}

// Modal functions for Loại truyện
function openAddLoaiTruyenModal() {
    document.getElementById('loaiTruyenModalTitle').textContent = 'Thêm loại truyện mới';
    document.getElementById('loaiTruyenAction').value = 'addLoaiTruyen';
    document.getElementById('loaiTruyenForm').reset();
    document.getElementById('id_loai_truyen_modal').value = '';
    document.getElementById('loaiTruyenModal').style.display = 'block';
}

function closeLoaiTruyenModal() {
    document.getElementById('loaiTruyenModal').style.display = 'none';
}

async function editLoaiTruyen(id) {
    const response = await fetch('admin.php?action=getLoaiTruyen');
    const data = await response.json();
    const loaitruyen = data.find(lt => lt.id_loai_truyen == id);
    
    if (loaitruyen) {
        document.getElementById('loaiTruyenModalTitle').textContent = 'Sửa loại truyện';
        document.getElementById('loaiTruyenAction').value = 'updateLoaiTruyen';
        document.getElementById('id_loai_truyen_modal').value = loaitruyen.id_loai_truyen;
        document.getElementById('ten_loai_truyen').value = loaitruyen.ten_loai_truyen;
        document.getElementById('loaiTruyenModal').style.display = 'block';
    }
}

async function deleteLoaiTruyen(id) {
    if (confirm('Bạn có chắc chắn muốn xóa loại truyện này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteLoaiTruyen&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa loại truyện thành công!');
                loadLoaiTruyenData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa loại truyện'));
            }
        } catch (error) {
            console.error('Error deleting loai truyen:', error);
            alert('Có lỗi xảy ra khi xóa loại truyện');
        }
    }
}

// Modal functions for Tài khoản
function closeTaiKhoanModal() {
    document.getElementById('taiKhoanModal').style.display = 'none';
}

async function editTaiKhoan(id) {
    try {
        const response = await fetch(`admin.php?action=getTaiKhoanDetail&id=${id}`);
        const user = await response.json();
        
        document.getElementById('id_nguoidung').value = user.id_nguoidung;
        document.getElementById('ten_dang_nhap_modal').value = user.ten_dang_nhap;
        document.getElementById('email_modal').value = user.email;
        document.getElementById('vai_tro_modal').value = user.vai_tro;
        document.getElementById('taiKhoanModal').style.display = 'block';
    } catch (error) {
        console.error('Error loading user detail:', error);
        alert('Có lỗi xảy ra khi tải thông tin tài khoản');
    }
}

async function deleteTaiKhoan(id) {
    if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteTaiKhoan&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa tài khoản thành công!');
                loadTaiKhoanData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa tài khoản'));
            }
        } catch (error) {
            console.error('Error deleting tai khoan:', error);
            alert('Có lỗi xảy ra khi xóa tài khoản');
        }
    }
}

// Load options
async function loadLoaiTruyenOptions() {
    try {
        const response = await fetch('admin.php?action=getLoaiTruyen');
        const data = await response.json();
        
        const select = document.querySelector('select[name="id_loai_truyen"]');
        select.innerHTML = '<option value="">Chọn loại truyện</option>';
        
        data.forEach(loai => {
            const option = document.createElement('option');
            option.value = loai.id_loai_truyen;
            option.textContent = loai.ten_loai_truyen;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading loai truyen options:', error);
    }
}

async function loadTheLoaiCheckboxes() {
    try {
        const response = await fetch('admin.php?action=getTheLoai');
        const data = await response.json();
        
        const container = document.getElementById('theLoaiList');
        container.innerHTML = '';
        
        data.forEach(theloai => {
            const label = document.createElement('label');
            label.innerHTML = `
                <input type="checkbox" name="the_loai[]" value="${theloai.id_theloai}">
                ${escapeHtml(theloai.ten_theloai)}
            `;
            container.appendChild(label);
        });
    } catch (error) {
        console.error('Error loading the loai checkboxes:', error);
    }
}

// Form submit handlers
document.getElementById('truyenForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(formData.get('action') === 'addTruyen' ? 'Thêm truyện thành công!' : 'Cập nhật truyện thành công!');
            closeTruyenModal();
            loadTruyenData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể thực hiện thao tác'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

document.getElementById('theLoaiForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(formData.get('action') === 'addTheLoai' ? 'Thêm thể loại thành công!' : 'Cập nhật thể loại thành công!');
            closeTheLoaiModal();
            loadTheLoaiData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể thực hiện thao tác'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

document.getElementById('loaiTruyenForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(formData.get('action') === 'addLoaiTruyen' ? 'Thêm loại truyện thành công!' : 'Cập nhật loại truyện thành công!');
            closeLoaiTruyenModal();
            loadLoaiTruyenData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể thực hiện thao tác'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

document.getElementById('taiKhoanForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Cập nhật tài khoản thành công!');
            closeTaiKhoanModal();
            loadTaiKhoanData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể cập nhật tài khoản'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default view
    showTruyen();
});
    </script>
</body>
</html>