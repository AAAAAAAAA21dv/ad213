<?php
require_once '../config.php';
requireLogin();

// Get all announcements
$stmt = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyurular</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1a0527;
            min-height: 100vh;
            color: #c9a9ff;
        }

        .announcement-card {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(138, 63, 242, 0.2);
        }

        .announcement-date {
            color: rgba(201, 169, 255, 0.6);
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Duyurular</h1>
            <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                Geri Dön
            </a>
        </div>

        <!-- Announcements List -->
        <div class="space-y-6">
            <?php if (empty($announcements)): ?>
                <div class="announcement-card text-center">
                    <i class="fas fa-bell text-4xl mb-4 text-purple-400"></i>
                    <p>Henüz hiç duyuru yok.</p>
                </div>
            <?php else: ?>
                <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-card">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-semibold text-white">
                                <?php echo htmlspecialchars($announcement['title']); ?>
                            </h3>
                            <span class="announcement-date">
                                <?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?>
                            </span>
                        </div>
                        <div class="prose prose-invert max-w-none">
                            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
