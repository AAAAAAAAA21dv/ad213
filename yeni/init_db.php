<?php
require_once 'config.php';

// Drop and recreate Users table with correct schema
$conn->exec("DROP TABLE IF EXISTS users");
$conn->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL UNIQUE, email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, balance DECIMAL(10,2) DEFAULT 0.00, is_admin INTEGER DEFAULT 0, is_banned INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");

// Create admin user if not exists
$adminEmail = "admin@admin.com";
$adminPassword = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$adminEmail]);

if (!$stmt->fetch()) {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)");
    $stmt->execute(['admin', $adminEmail, $adminPassword]);
    echo "Admin kullanıcı oluşturuldu.\n";
    echo "Email: admin@admin.com\n";
    echo "Şifre: admin123\n";
} else {
    echo "Admin kullanıcı zaten mevcut.\n";
}

// Drop and recreate Products table
$conn->exec("DROP TABLE IF EXISTS products");
$conn->exec("CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url TEXT,
    announcement_msg TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Drop and recreate Software table
$conn->exec("DROP TABLE IF EXISTS software");
$conn->exec("CREATE TABLE software (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    version TEXT NOT NULL,
    download_url TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Announcements table
$conn->exec("CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Purchases table
$conn->exec("CREATE TABLE IF NOT EXISTS purchases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)");

// Create admin user if not exists
$adminEmail = "admin@admin.com";
$adminPassword = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$adminEmail]);

if (!$stmt->fetch()) {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)");
    $stmt->execute(['admin', $adminEmail, $adminPassword]);
    echo "Admin kullanıcı oluşturuldu.\n";
    echo "Email: admin@admin.com\n";
    echo "Şifre: admin123\n";
} else {
    echo "Admin kullanıcı zaten mevcut.\n";
}

// Insert sample announcements
$conn->exec("INSERT INTO announcements (title, content) VALUES 
    ('Hoş Geldiniz!', 'Sitemize hoş geldiniz! Yeni ürünlerimizi ve yazılımlarımızı keşfedin.'),
    ('Yeni Özellikler', 'Bakiye yükleme sistemi artık aktif! Hemen bakiye yükleyerek alışverişe başlayabilirsiniz.')
");

// Insert sample products
$conn->exec("INSERT INTO products (name, description, price) VALUES 
    ('Premium Üyelik', 'Tüm özelliklere sınırsız erişim', 99.99),
    ('VIP Paket', 'Özel indirimler ve öncelikli destek', 199.99)
");

// Insert sample software
$conn->exec("INSERT INTO software (name, description, version, download_url) VALUES 
    ('Test Yazılımı', 'Test amaçlı örnek yazılım', '1.0.0', '/downloads/test.zip'),
    ('Demo Uygulama', 'Demo sürüm', '2.1.0', '/downloads/demo.zip')
");

echo "Veritabanı başarıyla oluşturuldu ve örnek veriler eklendi.\n";
?>
