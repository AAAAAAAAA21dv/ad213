<?php
require_once '../config.php';
requireAdmin();

$success = '';
$error = '';

// Handle software actions (create, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $stmt = $conn->prepare("INSERT INTO software (name, description, version, download_url) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['version'],
                    $_POST['download_url']
                ]);
                $success = "Yazılım başarıyla oluşturuldu.";
            } catch (Exception $e) {
                $error = "Yazılım oluşturulurken bir hata oluştu.";
            }
        } elseif ($_POST['action'] === 'edit') {
            try {
                $stmt = $conn->prepare("UPDATE software SET name = ?, description = ?, version = ?, download_url = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['version'],
                    $_POST['download_url'],
                    $_POST['software_id']
                ]);
                $success = "Yazılım başarıyla güncellendi.";
            } catch (Exception $e) {
                $error = "Yazılım güncellenirken bir hata oluştu.";
            }
        } elseif ($_POST['action'] === 'delete') {
            try {
                $stmt = $conn->prepare("DELETE FROM software WHERE id = ?");
                $stmt->execute([$_POST['software_id']]);
                $success = "Yazılım başarıyla silindi.";
            } catch (Exception $e) {
                $error = "Yazılım silinirken bir hata oluştu.";
            }
        }
    }
}

// Get all software
$stmt = $conn->query("SELECT * FROM software ORDER BY created_at DESC");
$software_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yazılım Yönetimi</title>
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

        .version-badge {
            background: rgba(138, 63, 242, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            color: rgb(216, 180, 254);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Yazılım Yönetimi</h1>
            <div class="flex gap-4">
                <button onclick="showCreateModal()" class="button button-green">
                    <i class="fas fa-plus"></i>
                    Yeni Yazılım
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

        <!-- Software List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($software_list as $software): ?>
                <div class="admin-card">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-semibold text-white">
                                    <?php echo htmlspecialchars($software['name']); ?>
                                </h3>
                                <span class="version-badge">
                                    v<?php echo htmlspecialchars($software['version']); ?>
                                </span>
                            </div>
                            
                            <p class="text-purple-300 mb-4">
                                <?php echo nl2br(htmlspecialchars($software['description'])); ?>
                            </p>

                            <a href="<?php echo htmlspecialchars($software['download_url']); ?>" 
                               target="_blank"
                               class="button button-purple">
                                <i class="fas fa-download"></i>
                                İndir
                            </a>
                        </div>
                        
                        <div class="flex gap-2">
                            <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($software)); ?>)" 
                                    class="button button-purple">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <form method="POST" class="inline" onsubmit="return confirm('Bu yazılımı silmek istediğinizden emin misiniz?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="software_id" value="<?php echo $software['id']; ?>">
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

    <!-- Create Software Modal -->
    <div id="createModal" class="modal">
        <div class="admin-card w-full max-w-md m-auto">
            <h2 class="text-2xl font-bold text-white mb-4">Yeni Yazılım Ekle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Yazılım Adı</label>
                        <input type="text" name="name" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Açıklama</label>
                        <textarea name="description" class="input-field" rows="3" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Versiyon</label>
                        <input type="text" name="version" class="input-field" required 
                               placeholder="Örn: 1.0.0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">İndirme Linki</label>
                        <input type="url" name="download_url" class="input-field" required>
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

    <!-- Edit Software Modal -->
    <div id="editModal" class="modal">
        <div class="admin-card w-full max-w-md m-auto">
            <h2 class="text-2xl font-bold text-white mb-4">Yazılımı Düzenle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="software_id" id="editSoftwareId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Yazılım Adı</label>
                        <input type="text" name="name" id="editName" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Açıklama</label>
                        <textarea name="description" id="editDescription" class="input-field" rows="3" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Versiyon</label>
                        <input type="text" name="version" id="editVersion" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">İndirme Linki</label>
                        <input type="url" name="download_url" id="editDownloadUrl" class="input-field" required>
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

        function showEditModal(software) {
            document.getElementById('editSoftwareId').value = software.id;
            document.getElementById('editName').value = software.name;
            document.getElementById('editDescription').value = software.description;
            document.getElementById('editVersion').value = software.version;
            document.getElementById('editDownloadUrl').value = software.download_url;
            document.getElementById('editModal').classList.add('active');
        }

        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
    </script>
</body>
</html>
