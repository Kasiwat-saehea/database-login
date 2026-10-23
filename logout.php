<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบ Method และ CSRF Token ตามเกณฑ์ความปลอดภัย TODO 6
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['csrf']) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        // ล้างข้อมูลใน session ทั้งหมด
        $_SESSION = [];

        // ลบคุกกี้ session ในเบราว์เซอร์
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // ทำลาย session
        session_destroy();
    }
}

// เด้งกลับไปหน้า login.php
header("Location: login.php");
exit;