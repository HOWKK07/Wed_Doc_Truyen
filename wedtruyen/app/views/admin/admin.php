<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
    header("Location: ../taiKhoan/login.php");
    exit();
}

// Xử lý các yêu cầu AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
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
            
        case 'getStats':
            $stats = [];
            
            // Đếm tài khoản
            $result = $conn->query("SELECT COUNT(*) as count FROM nguoidung");
            $stats['accounts'] = $result->fetch_assoc()['count'];
            
            // Đếm truyện
            $result = $conn->query("SELECT COUNT(*) as count FROM truyen");
            $stats['stories'] = $result->fetch_assoc()['count'];
            
            // Đếm thể loại
            $result = $conn->query("SELECT COUNT(*) as count FROM theloai");
            $stats['genres'] = $result->fetch_assoc()['count'];
            
            // Đếm loại truyện
            $result = $conn->query("SELECT COUNT(*) as count FROM loai_truyen");
            $stats['types'] = $result->fetch_assoc()['count'];
            
            echo json_encode($stats);
            exit();
            
        case 'deleteTruyen':
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                
                // Lấy ảnh bìa để xóa
                $stmt = $conn->prepare("SELECT anh_bia FROM truyen WHERE id_truyen = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $truyen = $result->fetch_assoc();
                
                // Xóa truyện
                $stmt = $conn->prepare("DELETE FROM truyen WHERE id_truyen = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    // Xóa file ảnh bìa
                    if ($truyen && $truyen['anh_bia'] && file_exists("../../../" . $truyen['anh_bia'])) {
                        unlink("../../../" . $truyen['anh_bia']);
                    }
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Không thể xóa truyện']);
                }
            }
            exit();
    }
}

// Xử lý thêm truyện mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addTruyen') {
    $ten_truyen = $_POST['ten_truyen'];
    $tac_gia = $_POST['tac_gia'];
    $id_loai_truyen = $_POST['id_loai_truyen'];
    $mo_ta = $_POST['mo_ta'];
    $trang_thai = $_POST['trang_thai'];
    $nam_phat_hanh = $_POST['nam_phat_hanh'];
    $the_loai = $_POST['the_loai'] ?? [];
    
    // Xử lý upload ảnh bìa
    $anh_bia = null;
    if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['error'] === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['anh_bia']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $file_path = "../../../uploads/anhbia/" . $file_name;
        
        if (!is_dir("../../../uploads/anhbia/")) {
            mkdir("../../../uploads/anhbia/", 0777, true);
        }
        
        if (move_uploaded_file($_FILES['anh_bia']['tmp_name'], $file_path)) {
            $anh_bia = "uploads/anhbia/" . $file_name;
        }
    }
    
    // Thêm truyện vào database
    $stmt = $conn->prepare("INSERT INTO truyen (ten_truyen, tac_gia, id_loai_truyen, anh_bia, mo_ta, trang_thai, nam_phat_hanh) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssi", $ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh);
    
    if ($stmt->execute()) {
        $id_truyen = $conn->insert_id;
        
        // Thêm thể loại
        if (!empty($the_loai)) {
            $stmt_theloai = $conn->prepare("INSERT INTO truyen_theloai (id_truyen, id_theloai) VALUES (?, ?)");
            foreach ($the_loai as $id_theloai) {
                $stmt_theloai->bind_param("ii", $id_truyen, $id_theloai);
                $stmt_theloai->execute();
            }
        }
        
        echo json_encode(['success' => true, 'id' => $id_truyen]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể thêm truyện']);
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
                        <tbody>
                            <!-- Activity log will be loaded here -->
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
            
            <!-- Other Views... -->
        </div>
    </div>
    
    <!-- Add Truyện Modal -->
    <div id="addTruyenModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Thêm truyện mới</h3>
                <span class="close-btn" onclick="closeAddTruyenModal()">&times;</span>
            </div>
            <form id="addTruyenForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addTruyen">
                <!-- Form fields... -->
            </form>
        </div>
    </div>
    
    <script src="/Wed_Doc_Truyen/wedtruyen/assets/js/admin/dashboard.js"></script>
</body>
</html>
