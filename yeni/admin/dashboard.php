<?php
require_once '../config.php';
requireAdmin();

// Get statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn(),
    'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'revenue' => $conn->query("SELECT COALESCE(SUM(price), 0) FROM purchases")->fetchColumn(),
    'software' => $conn->query("SELECT COUNT(*) FROM software")->fetchColumn()
];

// Get recent purchases
$stmt = $conn->query("
    SELECT p.*, u.username, pr.name as product_name 
    FROM purchases p 
    JOIN users u ON p.user_id = u.id 
    JOIN products pr ON p.product_id = pr.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$recent_purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent announcements
$stmt = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
$recent_announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1a0527;
            min-height: 100vh;
            color: #c9a9ff;
            position: relative;
            overflow-x: hidden;
        }

        .aurora-container {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            background: radial-gradient(circle at 50% 50%, #2d1b4e, #1a0527);
        }

        .aurora {
            position: absolute;
            width: 100%;
            height: 100%;
            filter: blur(100px);
            mix-blend-mode: screen;
            animation: aurora-movement 20s ease infinite alternate;
        }

        .aurora-1 {
            background: linear-gradient(45deg, #ff00ff, #00ffff);
            opacity: 0.3;
        }

        .aurora-2 {
            background: linear-gradient(-45deg, #ff00ff, #00ffff);
            opacity: 0.2;
        }

        @keyframes aurora-movement {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }
            50% {
                transform: translate(-30%, -30%) rotate(180deg);
            }
            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .admin-card {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(138, 63, 242, 0.2);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            color: #c9a9ff;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(138, 63, 242, 0.2);
            transform: translateX(5px);
        }
    </style>
</head>
<body class="p-8">
    <div class="aurora-container">
        <div class="aurora aurora-1"></div>
        <div class="aurora aurora-2"></div>
    </div>

    <div class="max-w-7xl mx-auto relative z-10">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Admin Paneli</h1>
            <div class="flex items-center gap-4">
                <a href="../user/dashboard.php" class="text-purple-300 hover:text-white transition">
                    <i class="fas fa-user mr-2"></i>
                    Kullanıcı Paneli
                </a>
                <a href="../logout.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                    Çıkış Yap
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Navigation -->
            <div class="space-y-4">
                <a href="users.php" class="admin-card block">
                    <div class="nav-link">
                        <i class="fas fa-users text-xl"></i>
                        <span>Kullanıcılar</span>
                    </div>
                </a>

                <a href="products.php" class="admin-card block">
                    <div class="nav-link">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span>Ürünler</span>
                    </div>
                </a>

                <a href="software.php" class="admin-card block">
                    <div class="nav-link">
                        <i class="fas fa-download text-xl"></i>
                        <span>Yazılımlar</span>
                    </div>
                </a>

                <a href="announcements.php" class="admin-card block">
                    <div class="nav-link">
                        <i class="fas fa-bullhorn text-xl"></i>
                        <span>Duyurular</span>
                    </div>
                </a>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-3 space-y-8">
                <!-- Statistics -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="admin-card">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-purple-500/20 rounded-lg">
                                <i class="fas fa-users text-2xl text-purple-300"></i>
                            </div>
                            <div>
                                <p class="text-sm text-purple-300">Kullanıcılar</p>
                                <p class="text-2xl font-bold text-white"><?php echo $stats['users']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-purple-500/20 rounded-lg">
                                <i class="fas fa-shopping-cart text-2xl text-purple-300"></i>
                            </div>
                            <div>
                                <p class="text-sm text-purple-300">Ürünler</p>
                                <p class="text-2xl font-bold text-white"><?php echo $stats['products']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-purple-500/20 rounded-lg">
                                <i class="fas fa-coins text-2xl text-purple-300"></i>
                            </div>
                            <div>
                                <p class="text-sm text-purple-300">Toplam Gelir</p>
                                <p class="text-2xl font-bold text-white">₺<?php echo number_format($stats['revenue'], 2); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-purple-500/20 rounded-lg">
                                <i class="fas fa-download text-2xl text-purple-300"></i>
                            </div>
                            <div>
                                <p class="text-sm text-purple-300">Yazılımlar</p>
                                <p class="text-2xl font-bold text-white"><?php echo $stats['software']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Purchases -->
                    <div class="admin-card">
                        <h2 class="text-xl font-semibold text-white mb-4">Son Satın Alımlar</h2>
                        <?php if (empty($recent_purchases)): ?>
                            <p class="text-center text-purple-300 py-4">
                                Henüz hiç satın alım yok.
                            </p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_purchases as $purchase): ?>
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-white">
                                                <?php echo htmlspecialchars($purchase['product_name']); ?>
                                            </h3>
                                            <p class="text-sm text-purple-300">
                                                <?php echo htmlspecialchars($purchase['username']); ?> tarafından
                                            </p>
                                            <p class="text-xs text-purple-400">
                                                <?php echo date('d.m.Y H:i', strtotime($purchase['created_at'])); ?>
                                            </p>
                                        </div>
                                        <span class="font-semibold text-white">
                                            ₺<?php echo number_format($purchase['price'], 2); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Announcements -->
                    <div class="admin-card">
                        <h2 class="text-xl font-semibold text-white mb-4">Son Duyurular</h2>
                        <?php if (empty($recent_announcements)): ?>
                            <p class="text-center text-purple-300 py-4">
                                Henüz hiç duyuru yok.
                            </p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_announcements as $announcement): ?>
                                    <div>
                                        <h3 class="font-semibold text-white">
                                            <?php echo htmlspecialchars($announcement['title']); ?>
                                        </h3>
                                        <p class="text-sm text-purple-300 mt-1">
                                            <?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
