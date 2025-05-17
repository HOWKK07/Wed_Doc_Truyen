<?php
class ApiResponse {
    public static function json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    public static function success($data = null, $message = "Success") {
        return self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = "Error", $status = 400) {
        return self::json([
            'status' => 'error',
            'message' => $message
        ], $status);
    }

    public static function unauthorized($message = "Unauthorized") {
        return self::error($message, 401);
    }

    public static function forbidden($message = "Forbidden") {
        return self::error($message, 403);
    }

    public static function notFound($message = "Not Found") {
        return self::error($message, 404);
    }
}
?> 