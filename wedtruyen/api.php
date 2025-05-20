<?php
// api.php: Entrypoint cho các API RESTful
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/app/config/connect.php';

// Lấy path và method
$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';
$segments = explode('/', trim($path, '/'));

// Helper trả về lỗi
function error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

// Định tuyến cơ bản
switch ($segments[0]) {
    case 'truyen':
        require_once __DIR__ . '/app/controllers/truyenController.php';
        $controller = new TruyenController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getTruyenById($segments[1]);
            } else {
                $controller->getAllTruyen();
            }
        } else if ($method === 'POST') {
            $controller->createTruyen();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing truyen id', 400);
            $controller->updateTruyen($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing truyen id', 400);
            $controller->deleteTruyen($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'user':
        require_once __DIR__ . '/app/controllers/taiKhoanController.php';
        $controller = new TaiKhoanController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getUserById($segments[1]);
            } else {
                $controller->getAllUsers();
            }
        } else if ($method === 'POST') {
            $controller->createUser();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing user id', 400);
            $controller->updateUser($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing user id', 400);
            $controller->deleteUser($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'comment':
        require_once __DIR__ . '/app/controllers/binhLuanController.php';
        $controller = new BinhLuanController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                echo $controller->getCommentById($segments[1]);
            } else {
                echo $controller->getAllComments();
            }
        } else if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo $controller->createComment($data);
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing comment id', 400);
            $data = json_decode(file_get_contents('php://input'), true);
            echo $controller->updateComment($segments[1], $data);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing comment id', 400);
            echo $controller->deleteComment($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'rating':
        require_once __DIR__ . '/app/controllers/danhGiaController.php';
        $controller = new DanhGiaController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getRatingById($segments[1]);
            } else {
                $controller->getAllRatings();
            }
        } else if ($method === 'POST') {
            $controller->createRating();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing rating id', 400);
            $controller->updateRating($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing rating id', 400);
            $controller->deleteRating($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'follow':
        require_once __DIR__ . '/app/controllers/thuVienController.php';
        $controller = new ThuVienController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getFollowById($segments[1]);
            } else {
                $controller->getAllFollows();
            }
        } else if ($method === 'POST') {
            $controller->createFollow();
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing follow id', 400);
            $controller->deleteFollow($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'notification':
        require_once __DIR__ . '/app/controllers/notificationController.php';
        $controller = new NotificationController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getNotificationById($segments[1]);
            } else {
                $controller->getAllNotifications();
            }
        } else if ($method === 'POST') {
            $controller->createNotification();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing notification id', 400);
            $controller->updateNotification($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing notification id', 400);
            $controller->deleteNotification($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'chapter':
        require_once __DIR__ . '/app/controllers/chapterController.php';
        $controller = new ChapterController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getChapterById($segments[1]);
            } else {
                $controller->getAllChapters();
            }
        } else if ($method === 'POST') {
            $controller->createChapter();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing chapter id', 400);
            $controller->updateChapter($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing chapter id', 400);
            $controller->deleteChapter($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'theloai':
        require_once __DIR__ . '/app/controllers/theLoaiController.php';
        $controller = new TheLoaiController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getTheLoaiById($segments[1]);
            } else {
                $controller->getAllTheLoai();
            }
        } else if ($method === 'POST') {
            $controller->createTheLoai();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing theloai id', 400);
            $controller->updateTheLoai($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing theloai id', 400);
            $controller->deleteTheLoai($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'loaiTruyen':
        require_once __DIR__ . '/app/controllers/loaiTruyenController.php';
        $controller = new LoaiTruyenController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getLoaiTruyenById($segments[1]);
            } else {
                $controller->getAllLoaiTruyen();
            }
        } else if ($method === 'POST') {
            $controller->createLoaiTruyen();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing loaiTruyen id', 400);
            $controller->updateLoaiTruyen($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing loaiTruyen id', 400);
            $controller->deleteLoaiTruyen($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'anhChuong':
        require_once __DIR__ . '/app/controllers/anhChuongController.php';
        $controller = new AnhChuongController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getAnhChuongById($segments[1]);
            } else {
                $controller->getAllAnhChuong();
            }
        } else if ($method === 'POST') {
            $controller->createAnhChuong();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing anhChuong id', 400);
            $controller->updateAnhChuong($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing anhChuong id', 400);
            $controller->deleteAnhChuong($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'chapterComment':
        require_once __DIR__ . '/app/controllers/chapterCommentController.php';
        $controller = new ChapterCommentController($conn);
        if ($method === 'GET') {
            if (isset($segments[1])) {
                $controller->getChapterCommentById($segments[1]);
            } else {
                $controller->getAllChapterComments();
            }
        } else if ($method === 'POST') {
            $controller->createChapterComment();
        } else if ($method === 'PUT') {
            if (!isset($segments[1])) error('Missing chapterComment id', 400);
            $controller->updateChapterComment($segments[1]);
        } else if ($method === 'DELETE') {
            if (!isset($segments[1])) error('Missing chapterComment id', 400);
            $controller->deleteChapterComment($segments[1]);
        } else {
            error('Method not allowed', 405);
        }
        break;
    case 'search':
        require_once __DIR__ . '/app/controllers/truyenController.php';
        $controller = new TruyenController($conn);
        $keyword = isset($_GET['q']) ? $_GET['q'] : '';
        echo $controller->searchTruyen($keyword);
        break;
    // TODO: Thêm các resource khác như user, comment, rating, ...
    default:
        error('Not found', 404);
} 