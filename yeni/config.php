<?php
session_start();

try {
    $conn = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Kullanıcının giriş yapıp yapmadığını kontrol eden fonksiyon
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Admin kontrolü yapan fonksiyon
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Giriş yapmamış kullanıcıları giriş sayfasına yönlendiren fonksiyon
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: giris.php");
        exit;
    }

    global $conn;
    $stmt = $conn->prepare("SELECT is_banned FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['is_banned'] == 1) {
        session_destroy();
        header("Location: banned.php");
        exit;
    }
}

// Sadece adminlerin erişebileceği sayfalar için kontrol fonksiyonu
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: ../user/dashboard.php");
        exit;
    }
}
?>
