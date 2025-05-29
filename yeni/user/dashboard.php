<?php
require_once '../config.php';
requireLogin();

$username = $_SESSION['username'];

// Get user's balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$balance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];

// Get recent announcements
$stmt = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent purchases
$stmt = $conn->prepare("
    SELECT p.*, pr.name as product_name, pr.announcement_msg
    FROM purchases p 
    JOIN products pr ON p.product_id = pr.id 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kullanıcı Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap"
      rel="stylesheet"
    />
    <style>
      body {
        font-family: "Poppins", sans-serif;
        background-color: #1a0527;
        min-height: 100vh;
        color: #c9a9ff;
        position: relative;
        overflow-x: hidden;
      }

      /* Animated Background */
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

      .dashboard-card {
        background: rgba(63, 26, 122, 0.2);
        border: 1px solid rgba(138, 63, 242, 0.3);
        border-radius: 1rem;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
      }

      .dashboard-card:hover {
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

      .nav-link.active {
        background: rgba(138, 63, 242, 0.3);
        color: white;
      }

      .announcement-item {
        padding: 1rem;
        border-bottom: 1px solid rgba(138, 63, 242, 0.2);
      }

      .announcement-item:last-child {
        border-bottom: none;
      }

      .balance-message {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 1rem;
        border-radius: 1rem;
        color: #d1aaffcc;
        margin-bottom: 1rem;
        font-style: italic;
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
            <h1 class="text-3xl font-bold text-white">
                Hoş Geldiniz, <?php echo htmlspecialchars($username); ?>
            </h1>
            <div class="flex items-center gap-4">
                <span class="text-lg">
                    Bakiye: <span class="font-bold text-white">₺<?php echo number_format($balance, 2); ?></span>
                </span>
                <a href="../logout.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                    Çıkış Yap
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Navigation -->
            <div class="space-y-4">
                <a href="products.php" class="dashboard-card block">
                    <div class="nav-link">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span>Ürünler</span>
                    </div>
                </a>

                <a href="software.php" class="dashboard-card block">
                    <div class="nav-link">
                        <i class="fas fa-download text-xl"></i>
                        <span>Yazılımlar</span>
                    </div>
                </a>

                <a href="announcements.php" class="dashboard-card block">
                    <div class="nav-link">
                        <i class="fas fa-bell text-xl"></i>
                        <span>Duyurular</span>
                    </div>
                </a>

                <a href="balance.php" class="dashboard-card block">
                    <div class="nav-link">
                        <i class="fas fa-wallet text-xl"></i>
                        <span>Bakiye Yükle</span>
                    </div>
                </a>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-3 space-y-8">
                <div class="balance-message">
                    Bakiye yüklemek istiyorsanız lütfen satıcı ile iletişime geçin.
                </div>

                <!-- Recent Announcements -->
                <div class="dashboard-card">
                    <h2 class="text-xl font-semibold text-white mb-4">Son Duyurular</h2>
                    <?php if (empty($announcements)): ?>
                        <p class="text-center text-purple-300 py-4">
                            Henüz hiç duyuru yok.
                        </p>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-item">
                                <h3 class="font-semibold text-white">
                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                </h3>
                                <p class="text-sm text-purple-300 mt-1">
                                    <?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                        <div class="mt-4 text-right">
                            <a href="announcements.php" class="text-purple-400 hover:text-purple-300">
                                Tüm duyuruları gör →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Purchases -->
                <div class="dashboard-card">
                    <h2 class="text-xl font-semibold text-white mb-4">Son Satın Alımlar</h2>
                    <?php if (empty($purchases)): ?>
                        <p class="text-center text-purple-300 py-4">
                            Henüz hiç satın alım yapmadınız.
                        </p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($purchases as $purchase): ?>
                                <div class="flex justify-between items-center">
                                    <div class="w-full">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-semibold text-white">
                                                    <?php echo htmlspecialchars($purchase['product_name']); ?>
                                                </h3>
                                                <p class="text-sm text-purple-300">
                                                    <?php echo date('d.m.Y H:i', strtotime($purchase['created_at'])); ?>
                                                </p>
                                            </div>
                                            <span class="font-semibold text-white">
                                                ₺<?php echo number_format($purchase['price'], 2); ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($purchase['announcement_msg'])): ?>
                                            <div class="mt-2 p-3 bg-purple-900/30 border-l-4 border-purple-500 rounded">
                                                <p class="text-sm text-purple-200 font-mono">
                                                    <?php echo nl2br(htmlspecialchars($purchase['announcement_msg'])); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
