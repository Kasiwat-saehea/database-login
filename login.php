<?php
session_start();
require_once __DIR__ . '/config/db.php';

// ถ้าล็อกอินอยู่แล้วให้ไปที่ skills.php ทันที
if (isset($_SESSION["user_id"])) {
    header("Location: skills.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'กรุณากรอก Email และ Password ให้ครบถ้วน';
    } else {
        // TODO 1 & 2: Prepared statement ค้นหาผู้ใช้ด้วย email
        $sql = "SELECT id, profile_id, password_hash FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            // TODO 3: ตรวจสอบรหัสผ่านด้วย password_verify
            if ($user && password_verify($password, $user['password_hash'])) {
                // TODO 4: ป้องกัน Session Fixation และบันทึกข้อมูลเข้า Session
                session_regenerate_id(true);
                $_SESSION["user_id"] = (int)$user['id'];
                $_SESSION["profile_id"] = isset($user['profile_id']) ? (int)$user['profile_id'] : (int)$user['id'];
                $_SESSION["email"] = $email;

                mysqli_stmt_close($stmt);
                
                // TODO 5: เปลี่ยนหน้าไป skills.php
                header("Location: skills.php");
                exit;
            } else {
                $error = 'Email หรือ Password ไม่ถูกต้อง';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Login | เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="assets/css/crud.css">
    <style>
        .login-box { max-width: 380px; margin: 80px auto; padding: 24px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #ddd; font-family: sans-serif; }
        .login-box h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-login { width: 100%; padding: 10px; background: #0066cc; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn-login:hover { background: #0052a3; }
        .err-msg { background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; text-align: center; border: 1px solid #ef9a9a; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>เข้าสู่ระบบ (Login)</h2>
        <?php if ($error): ?>
            <div class="err-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required placeholder="student@example.com">
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required placeholder="123456">
            </div>
            <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
        </form>
    </div>
</body>
</html>