<?php
require_once '../config.php';
requireAdmin();

$success = '';
$error = '';

// Handle user deletion and balance addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete') {
            try {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
                $stmt->execute([$_POST['user_id']]);
                if ($stmt->rowCount() > 0) {
                    $success = "Kullanıcı başarıyla silindi.";
                } else {
                    $error = "Kullanıcı silinemedi. Admin kullanıcılar silinemez.";
                }
            } catch (Exception $e) {
                $error = "Kullanıcı silinirken bir hata oluştu.";
            }
        } elseif ($_POST['action'] === 'add_balance') {
            try {
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([floatval($_POST['amount']), $_POST['user_id']]);
                $success = "Bakiye başarıyla eklendi.";
            } catch (Exception $e) {
                $error = "Bakiye eklenirken bir hata oluştu.";
            }
        }
    }
}

// Get all users with their purchase counts and total spent
$stmt = $conn->query("
    SELECT 
        users.*, 
        COUNT(DISTINCT purchases.id) as purchase_count,
        SUM(purchases.price) as total_spent
    FROM users 
    LEFT JOIN purchases ON users.id = purchases.user_id 
    GROUP BY users.id 
    ORDER BY users.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get user's purchase history
function getUserPurchases($userId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT 
            purchases.*,
            products.name as product_name,
            products.description as product_description
        FROM purchases 
        JOIN products ON purchases.product_id = products.id
        WHERE purchases.user_id = ?
        ORDER BY purchases.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        }
        .button {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .button-red { background: rgba(239, 68, 68, 0.2); color: rgb(252, 165, 165); }
        .button-purple { background: rgba(138, 63, 242, 0.2); color: rgb(216, 180, 254); }
        .button-green { background: rgba(34, 197, 94, 0.2); color: rgb(134, 239, 172); }
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 50;
        }
        .modal.active { display: flex; }
        .input-field {
            background: rgba(138, 63, 242, 0.1);
            border: 1px solid rgba(138, 63, 242, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Kullanıcı Yönetimi</h1>
            <a href="dashboard.php" class="button button-purple">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Panele Dön
            </a>
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

        <div class="space-y-6">
            <?php foreach ($users as $user): ?>
                <div class="admin-card">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-semibold text-white">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </h3>
                                <?php if ($user['is_admin']): ?>
                                    <span class="px-2 py-1 bg-purple-500/20 text-purple-200 rounded-full text-sm">Admin</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-purple-300 mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-purple-900/20 p-3 rounded-lg">
                                    <p class="text-sm text-purple-400">Bakiye</p>
                                    <p class="text-lg font-semibold text-white">₺<?php echo number_format($user['balance'], 2); ?></p>
                                </div>
                                <div class="bg-purple-900/20 p-3 rounded-lg">
                                    <p class="text-sm text-purple-400">Toplam Alışveriş</p>
                                    <p class="text-lg font-semibold text-white"><?php echo $user['purchase_count']; ?></p>
                                </div>
                                <div class="bg-purple-900/20 p-3 rounded-lg">
                                    <p class="text-sm text-purple-400">Toplam Harcama</p>
                                    <p class="text-lg font-semibold text-white">₺<?php echo number_format($user['total_spent'] ?? 0, 2); ?></p>
                                </div>
                            </div>

                            <?php if (!$user['is_admin']): ?>
                                <div class="mt-4 flex gap-2">
                                    <button onclick="showPurchases(<?php echo $user['id']; ?>)" class="button button-purple">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                        </svg>
                                        Satın Alma Geçmişi
                                    </button>
                                    <button onclick="showAddBalance(<?php echo $user['id']; ?>)" class="button button-green">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        Bakiye Ekle
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="button button-red">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            Kullanıcıyı Sil
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Purchases Modal -->
                <div id="purchasesModal<?php echo $user['id']; ?>" class="modal">
                    <div class="m-auto bg-purple-900/95 p-6 rounded-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-white">
                                <?php echo htmlspecialchars($user['username']); ?> - Satın Alma Geçmişi
                            </h3>
                            <button onclick="hideModal('purchasesModal<?php echo $user['id']; ?>')" class="text-purple-300 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <?php 
                            $purchases = getUserPurchases($user['id']);
                            if (empty($purchases)): 
                            ?>
                                <p class="text-purple-300 text-center py-4">Henüz satın alma işlemi yok.</p>
                            <?php else: foreach ($purchases as $purchase): ?>
                                <div class="bg-purple-800/20 p-4 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-white">
                                                <?php echo htmlspecialchars($purchase['product_name']); ?>
                                            </h4>
                                            <p class="text-sm text-purple-300">
                                                <?php echo htmlspecialchars($purchase['product_description']); ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-white">
                                                ₺<?php echo number_format($purchase['price'], 2); ?>
                                            </p>
                                            <p class="text-sm text-purple-300">
                                                <?php echo date('d.m.Y H:i', strtotime($purchase['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Add Balance Modal -->
                <div id="addBalanceModal<?php echo $user['id']; ?>" class="modal">
                    <div class="m-auto bg-purple-900/95 p-6 rounded-xl max-w-md w-full">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-white">
                                Bakiye Ekle - <?php echo htmlspecialchars($user['username']); ?>
                            </h3>
                            <button onclick="hideModal('addBalanceModal<?php echo $user['id']; ?>')" class="text-purple-300 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="add_balance">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Eklenecek Miktar (₺)</label>
                                <input type="number" name="amount" step="0.01" min="0" class="input-field" required>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="button button-green">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                    </svg>
                                    Bakiye Ekle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        function showPurchases(userId) {
            showModal('purchasesModal' + userId);
        }
        
        function showAddBalance(userId) {
            showModal('addBalanceModal' + userId);
        }
    </script>
</body>
</html>
