<?php
require_once '../config.php';
requireLogin();

// Get all software
$stmt = $conn->query("SELECT * FROM software ORDER BY created_at DESC");
$softwareList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Yazılımlar</title>
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
      }

      .software-card {
        background: rgba(63, 26, 122, 0.2);
        border: 1px solid rgba(138, 63, 242, 0.3);
        border-radius: 1rem;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
      }

      .software-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(138, 63, 242, 0.2);
      }

      .download-button {
        background: rgba(138, 63, 242, 0.8);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
      }

      .download-button:hover {
        background: rgba(138, 63, 242, 1);
        transform: translateY(-2px);
      }
    </style>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Yazılımlar</h1>
            <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                Geri Dön
            </a>
        </div>

        <!-- Software List -->
        <div class="space-y-6">
            <?php if (empty($softwareList)): ?>
                <div class="software-card text-center">
                    <i class="fas fa-download text-4xl mb-4 text-purple-400"></i>
                    <p>Henüz hiç yazılım yok.</p>
                </div>
            <?php else: ?>
                <?php foreach ($softwareList as $software): ?>
                    <div class="software-card">
                        <h3 class="text-xl font-semibold text-white mb-2">
                            <?php echo htmlspecialchars($software['name']); ?>
                        </h3>
                        <p class="text-purple-300 mb-4">
                            <?php echo htmlspecialchars($software['description']); ?>
                        </p>
                        <p class="text-purple-300 mb-4">
                            Sürüm: <?php echo htmlspecialchars($software['version']); ?>
                        </p>
                        <?php 
                          $downloadLink = (!empty($software['download_url'])) 
                                          ? htmlspecialchars($software['download_url']) 
                                          : '#';
                        ?>
                        <a href="<?php echo $downloadLink; ?>" 
                           class="download-button" 
                           <?php echo (!empty($software['download_url']) ? 'target="_blank" download' : 'title="İndirme linki henüz eklenmedi"'); ?>>
                            <i class="fas fa-download mr-2"></i> İndir
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
