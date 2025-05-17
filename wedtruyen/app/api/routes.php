<?php
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/StoryController.php';
require_once __DIR__ . '/controllers/ChapterController.php';
require_once __DIR__ . '/controllers/CommentController.php';
require_once __DIR__ . '/controllers/RatingController.php';
require_once __DIR__ . '/controllers/FollowController.php';
require_once __DIR__ . '/controllers/NotificationController.php';

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

// Remove base path from URI
$base_path = 'wedtruyen/app/api';
$base_path_parts = explode('/', $base_path);
$uri = array_diff($uri, $base_path_parts);
$uri = array_values($uri);

// API endpoints
$endpoint = $uri[0] ?? '';
$id = $uri[1] ?? null;
$action = $uri[2] ?? null;

// Initialize controllers
$userController = new UserController();
$storyController = new StoryController();
$chapterController = new ChapterController();
$commentController = new CommentController();
$ratingController = new RatingController();
$followController = new FollowController();
$notificationController = new NotificationController();

// Route handling
switch ($endpoint) {
    // User endpoints
    case 'users':
        switch ($method) {
            case 'POST':
                if ($action === 'register') {
                    echo $userController->register();
                } elseif ($action === 'login') {
                    echo $userController->login();
                } elseif ($action === 'change-password') {
                    echo $userController->changePassword();
                }
                break;
            case 'GET':
                if ($action === 'profile') {
                    echo $userController->getProfile();
                }
                break;
            case 'PUT':
                if ($action === 'profile') {
                    echo $userController->updateProfile();
                }
                break;
        }
        break;

    // Story endpoints
    case 'stories':
        switch ($method) {
            case 'GET':
                if ($id) {
                    echo $storyController->get($id);
                } else {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $search = $_GET['search'] ?? '';
                    $category = $_GET['category'] ?? null;
                    $type = $_GET['type'] ?? null;
                    echo $storyController->getAll($page, $limit, $search, $category, $type);
                }
                break;
            case 'POST':
                echo $storyController->create();
                break;
            case 'PUT':
                if ($id) {
                    echo $storyController->update($id);
                }
                break;
            case 'DELETE':
                if ($id) {
                    echo $storyController->delete($id);
                }
                break;
        }
        break;

    // Chapter endpoints
    case 'chapters':
        switch ($method) {
            case 'GET':
                if ($id) {
                    echo $chapterController->get($id);
                } elseif ($action === 'story' && isset($uri[3])) {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    echo $chapterController->getByStory($uri[3], $page, $limit);
                }
                break;
            case 'POST':
                echo $chapterController->create();
                break;
            case 'PUT':
                if ($id) {
                    echo $chapterController->update($id);
                }
                break;
            case 'DELETE':
                if ($id) {
                    echo $chapterController->delete($id);
                }
                break;
        }
        break;

    // Comment endpoints
    case 'comments':
        switch ($method) {
            case 'GET':
                if ($action === 'story' && isset($uri[3])) {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    echo $commentController->getStoryComments($uri[3], $page, $limit);
                } elseif ($action === 'chapter' && isset($uri[3])) {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    echo $commentController->getChapterComments($uri[3], $page, $limit);
                }
                break;
            case 'POST':
                if ($action === 'story') {
                    echo $commentController->createStoryComment();
                } elseif ($action === 'chapter') {
                    echo $commentController->createChapterComment();
                }
                break;
            case 'DELETE':
                if ($action === 'story' && isset($uri[3])) {
                    echo $commentController->deleteStoryComment($uri[3]);
                } elseif ($action === 'chapter' && isset($uri[3])) {
                    echo $commentController->deleteChapterComment($uri[3]);
                }
                break;
        }
        break;

    // Rating endpoints
    case 'ratings':
        switch ($method) {
            case 'GET':
                if ($action === 'story' && isset($uri[3])) {
                    echo $ratingController->getStoryRating($uri[3]);
                } elseif ($action === 'user' && isset($uri[3])) {
                    echo $ratingController->getUserRating($uri[3]);
                }
                break;
            case 'POST':
                echo $ratingController->rate();
                break;
            case 'DELETE':
                if ($action === 'story' && isset($uri[3])) {
                    echo $ratingController->deleteRating($uri[3]);
                }
                break;
        }
        break;

    // Follow endpoints
    case 'follows':
        switch ($method) {
            case 'GET':
                if ($action === 'stories') {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    echo $followController->getFollowedStories($page, $limit);
                } elseif ($action === 'check' && isset($uri[3])) {
                    echo $followController->checkFollow($uri[3]);
                }
                break;
            case 'POST':
                echo $followController->follow();
                break;
            case 'DELETE':
                if (isset($uri[1])) {
                    echo $followController->unfollow($uri[1]);
                }
                break;
        }
        break;

    // Notification endpoints
    case 'notifications':
        switch ($method) {
            case 'GET':
                if ($action === 'unread-count') {
                    echo $notificationController->getUnreadCount();
                } else {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    echo $notificationController->getNotifications($page, $limit);
                }
                break;
            case 'PUT':
                if ($action === 'read' && isset($uri[3])) {
                    echo $notificationController->markAsRead($uri[3]);
                } elseif ($action === 'read-all') {
                    echo $notificationController->markAllAsRead();
                }
                break;
            case 'DELETE':
                if (isset($uri[1])) {
                    echo $notificationController->delete($uri[1]);
                } elseif ($action === 'all') {
                    echo $notificationController->deleteAll();
                }
                break;
        }
        break;

    default:
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?> 