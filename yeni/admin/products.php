<?php
require_once '../config.php';
requireAdmin();

$success = '';
$error = '';

// Handle product actions (create, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, announcement_msg) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    floatval($_POST['price']),
                    $_POST['image_url'],
                    $_POST['announcement_msg']
                ]);
                $success = "Ürün başarıyla oluşturuldu.";
            } catch (Exception $e) {
                $error = "Ürün oluşturulurken bir hata oluştu.";
            }
        } elseif ($_POST['action'] === 'edit') {
            try {
                $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, announcement_msg = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    floatval($_POST['price']),
                    $_POST['image_url'],
                    $_POST['announcement_msg'],
                    $_POST['product_id']
                ]);
                $success = "Ürün başarıyla güncellendi.";
            } catch (Exception $e) {
                $error = "Ürün güncellenirken bir hata oluştu.";
            }
        } elseif ($_POST['action'] === 'delete') {
            try {
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$_POST['product_id']]);
                $success = "Ürün başarıyla silindi.";
            } catch (Exception $e) {
                $error = "Ürün silinirken bir hata oluştu.";
            }
        }
    }
}

// Get all products
$stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Yönetimi</title>
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
            <h1 class="text-3xl font-bold text-white">Ürün Yönetimi</h1>
            <div class="flex gap-4">
                <button onclick="showCreateModal()" class="button button-green">
                    <i class="fas fa-plus"></i>
                    Yeni Ürün
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

        <!-- Products List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="admin-card">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="w-full h-48 object-cover rounded-lg mb-4">
                    
                    <h3 class="text-xl font-semibold text-white mb-2">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    
                    <p class="text-purple-300 mb-4">
                        <?php echo htmlspecialchars($product['description']); ?>
                    </p>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-white">
                            ₺<?php echo number_format($product['price'], 2); ?>
                        </span>
                        
                        <div class="flex gap-2">
                            <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                                    class="button button-purple">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <form method="POST" class="inline" onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
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

    <!-- Create Product Modal -->
    <div id="createModal" class="modal">
        <div class="admin-card w-full max-w-md m-auto">
            <h2 class="text-2xl font-bold text-white mb-4">Yeni Ürün Ekle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Ürün Adı</label>
                        <input type="text" name="name" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Açıklama</label>
                        <textarea name="description" class="input-field" rows="3" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Fiyat (₺)</label>
                        <input type="number" name="price" step="0.01" min="0" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Resim URL</label>
                        <input type="url" name="image_url" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Satın Alım Sonrası Mesaj</label>
                        <textarea name="announcement_msg" class="input-field" rows="3" 
                                placeholder="Örnek: Discord: example#1234 | Kullanıcı Adı: test | Şifre: 123456"></textarea>
                        <p class="text-sm text-purple-400 mt-1">Bu mesaj kullanıcıya ürünü satın aldıktan sonra gösterilecektir.</p>
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

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="admin-card w-full max-w-md m-auto">
            <h2 class="text-2xl font-bold text-white mb-4">Ürünü Düzenle</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="product_id" id="editProductId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Ürün Adı</label>
                        <input type="text" name="name" id="editName" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Açıklama</label>
                        <textarea name="description" id="editDescription" class="input-field" rows="3" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Fiyat (₺)</label>
                        <input type="number" name="price" id="editPrice" step="0.01" min="0" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Resim URL</label>
                        <input type="url" name="image_url" id="editImageUrl" class="input-field" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Satın Alım Sonrası Mesaj</label>
                        <textarea name="announcement_msg" id="editAnnouncementMsg" class="input-field" rows="3" 
                                placeholder="Örnek: Discord: example#1234 | Kullanıcı Adı: test | Şifre: 123456"></textarea>
                        <p class="text-sm text-purple-400 mt-1">Bu mesaj kullanıcıya ürünü satın aldıktan sonra gösterilecektir.</p>
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

        function showEditModal(product) {
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editName').value = product.name;
            document.getElementById('editDescription').value = product.description;
            document.getElementById('editPrice').value = product.price;
            document.getElementById('editImageUrl').value = product.image_url;
            document.getElementById('editAnnouncementMsg').value = product.announcement_msg || '';
            document.getElementById('editModal').classList.add('active');
        }

        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
    </script>
</body>
</html>
