<?php
require_once 'config.php';

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function check_auth($required_type = null) {
    if (!is_logged_in()) {
        header("Location: " . SITE_URL . "/index.php");
        exit;
    }

    if ($required_type && $_SESSION['user_type'] !== $required_type) {
        // Eğer yetkisiz bir sayfaya girmeye çalışıyorsa kendi paneline yönlendir
        if ($_SESSION['user_type'] === 'student') {
            header("Location: " . SITE_URL . "/student_dashboard.php");
        } else {
            header("Location: " . SITE_URL . "/teacher_dashboard.php");
        }
        exit;
    }
}
?>
