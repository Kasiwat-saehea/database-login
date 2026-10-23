<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบสิทธิ์การเข้าใช้งาน: ถ้ายังไม่ได้ล็อกอิน ให้เด้งกลับไปหน้า login.php ทันที
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

// สร้าง CSRF Token ประจำ Session
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function h($v) { 
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); 
}

function csrf_field() { 
    echo '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">'; 
}

function verify_csrf() { 
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) { 
        http_response_code(403); 
        exit('Invalid CSRF token'); 
    } 
}

function valid_id($value) { 
    $n = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); 
    return $n === false ? null : $n; 
}

// ผูกโปรไฟล์ตามบัญชีผู้ใช้ที่ล็อกอินอยู่
function profile_id($conn) { 
    if (isset($_SESSION['profile_id']) && (int)$_SESSION['profile_id'] > 0) {
        return (int)$_SESSION['profile_id'];
    }
    $r = mysqli_query($conn, 'SELECT id FROM profile ORDER BY id LIMIT 1'); 
    $p = $r ? mysqli_fetch_assoc($r) : null; 
    return $p ? (int)$p['id'] : 0; 
}

function page_start($title) { 
    echo '<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . h($title) . '</title><link rel="stylesheet" href="assets/css/crud.css"></head><body><main class="wrap"><nav style="display:flex; align-items:center; gap:12px;"><a href="index.php">Resume</a><a href="skills.php">Manage Skills</a><a href="skill_create.php">+ Add Skill</a><form action="logout.php" method="POST" style="margin-left:auto; display:inline;"><input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '"><button type="submit" style="background:none; border:none; color:#dc3545; font-weight:bold; cursor:pointer; font-size:15px; font-family:inherit;">Logout</button></form></nav><div class="panel"><h1>' . h($title) . '</h1>'; 
}

function page_end() { 
    echo '</div></main></body></html>'; 
}