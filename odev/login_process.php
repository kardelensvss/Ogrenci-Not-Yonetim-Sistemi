<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_type = $_POST['login_type'] ?? '';
    
    if ($login_type === 'student') {
        $ogr_no = trim($_POST['ogr_no']);
        $password = trim($_POST['password']);
        
        $stmt = $db->prepare("SELECT id, ad, soyad FROM ogrenciler WHERE ogr_no = ? AND sifre = ?");
        $stmt->execute([$ogr_no, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['user_name'] = $user['ad'] . ' ' . $user['soyad'];
            header("Location: student_dashboard.php");
            exit;
        } else {
            header("Location: index.php?error=1");
            exit;
        }
    } 
    elseif ($login_type === 'teacher') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        $stmt = $db->prepare("SELECT id, ad, soyad FROM ogretmenler WHERE kullanici_adi = ? AND sifre = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'teacher';
            $_SESSION['user_name'] = $user['ad'] . ' ' . $user['soyad'];
            header("Location: teacher_dashboard.php");
            exit;
        } else {
            header("Location: index.php?error=1");
            exit;
        }
    }
}

header("Location: index.php");
exit;
?>
