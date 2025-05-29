<?php
require_once '../config.php';
requireAdmin();

$success = '';
$error = '';

// Handle announcement actions (create, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content']
                ]);
                $success = "Duyuru başarıyla oluşturuldu.";
            } catch (Exception $e) {
                $error = "Duyuru oluşturulurken bir hata oluştu.";
            }
        } elseif ($_POST['action'] === 'edit') {
            try {
                $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content'],
                    $_POST['announcement_id']
                ]);
                $success = "Duyuru başarıyla güncellendi.";
            } catch (Exception $e) {
                $error = "Duyuru güncellenirken bir hata oluştu.";
            }
        } elseif ($_POST['action'] === 'delete') {
            try {
                $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
                $stmt->execute([$_POST['announcement_id']]);
                $success = "Duyuru başarıyla silindi.";
            } catch (Exception $e) {
                $error = "Duyuru silinirken bir hata oluştu.";
            }
        }
    }
}

// Get all announcements
$stmt = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyuru Yönetimi</title>
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

        .button {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .button-red {
            background: rgba(239, 68, 68, 0.2);
            color: rgb(252, 165, 165);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .button-red:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        .button-green {
            background: rgba(34, 197, 94, 0.2);
            color: rgb(134, 239, 172);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .button-green:hover {
            background: rgba(34, 197, 94, 0.3);
        }

        .button-purple {
            background: rgba(138, 63, 242, 0.2);
            color: rgb(216, 180, 254);
            border: 1px solid rgba(138, 63, 242, 0.3);
        }

        .button-purple:hover {
            background: rgba(138, 63, 242, 0.3);
        }

        .input-field {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: rgba(138, 63, 242, 0.8);
            box-shadow: 0 0 15px rgba(138, 63, 242, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 50;
        }

        .modal.active {
            display: flex;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Duyuru Yönetimi</h1>
            <div class="flex gap-4">
                <button onclick="showCreateModal()" class="button button-green">
                    <i class="fas fa-plus"></i>
                    Yeni Duyuru
                </button>
                <a href="dashboard.php" class="button button-purple">
                    <i class="fas fa-arrow-left"></i>
                    Panele Dön
                </a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-500/20 border border-green-500/30 text-green-200 px-4 py-3 rounded-lg mb-6">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Announcements List -->
        <div class="space-y-6">
            <?php foreach ($announcements as $announcement): ?>
                <div class="admin-card">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-white mb-2">
                                <?php echo htmlspecialchars($announcement['title']); ?>
                            </h3>
                            
                            <p class="text-purple-300 mb-2">
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </p>
                            
                            <p class="text-sm text-purple-400">
                                <?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?>
                            </p>
                        </div>
                        
                        <div class="flex gap-2">
                            <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($announcement)); ?>)" 
                                    class="button button-purple">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <form method="POST" class="inline" onsubmit="return confirm('Bu duyuruyu silmek istediğinizden emin misiniz?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="announcement_id" value="<?php echo $announcement['id']; ?>">
                                <button type="submit" class="button button-red">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Create Announcement Modal -->
    <div id="createModal" class="modal">
        <div class="admin-card w-full max-w-md m-auto">
            <h2 class="text-2xl font-bold text-white mb-4">Yeni Duyuru Ekle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Başlık</label>
                        <input type="text" name="title" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">İçerik</label>
                        <textarea name="content" class="input-field" rows="5" required></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" 
                            onclick="hideModal('createModal')"
                            class="button button-red">
                        İptal
                    </button>
                    <button type="submit" class="button button-green">
                        Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Announcement Modal -->
    <div id="editModal" class="modal">
        <div class="admin-card w-full max-w-md m-auto">
            <h2 class="text-2xl font-bold text-white mb-4">Duyuruyu Düzenle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="announcement_id" id="editAnnouncementId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Başlık</label>
                        <input type="text" name="title" id="editTitle" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">İçerik</label>
                        <textarea name="content" id="editContent" class="input-field" rows="5" required></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" 
                            onclick="hideModal('editModal')"
                            class="button button-red">
                        İptal
                    </button>
                    <button type="submit" class="button button-green">
                        Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateModal() {
            document.getElementById('createModal').classList.add('active');
        }

        function showEditModal(announcement) {
            document.getElementById('editAnnouncementId').value = announcement.id;
            document.getElementById('editTitle').value = announcement.title;
            document.getElementById('editContent').value = announcement.content;
            document.getElementById('editModal').classList.add('active');
        }

        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
    </script>
</body>
</html>
