<?php
require_once '../config.php';
requireLogin();

$success = '';
$error = '';

// Get user's current balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentBalance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];

// Handle balance top-up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = floatval($_POST['amount']);
    
    if ($amount <= 0) {
        $error = "Geçerli bir miktar giriniz.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $_SESSION['user_id']]);
            
            // Refresh balance
            $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $currentBalance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];
            
            $success = "Bakiye başarıyla yüklendi!";
        } catch (Exception $e) {
            $error = "İşlem sırasında bir hata oluştu.";
        }
    }
}

// Get transaction history (purchases)
$stmt = $conn->prepare("
    SELECT p.*, pr.name as product_name, pr.price 
    FROM purchases p 
    JOIN products pr ON p.product_id = pr.id 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakiye Yönetimi</title>
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

        .balance-card {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .balance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(138, 63, 242, 0.2);
        }

        .input-field {
            background: rgba(63, 26, 122, 0.3);
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

        .submit-button {
            background: rgba(138, 63, 242, 0.8);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .submit-button:hover {
            background: rgba(138, 63, 242, 1);
            transform: translateY(-2px);
        }

        .transaction-list {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 1rem;
            overflow: hidden;
        }

        .transaction-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(138, 63, 242, 0.2);
            transition: all 0.3s ease;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-item:hover {
            background: rgba(138, 63, 242, 0.1);
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Bakiye Yönetimi</h1>
            <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                Geri Dön
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Balance Card -->
            <div class="balance-card">
                <h2 class="text-2xl font-semibold text-white mb-4">Mevcut Bakiye</h2>
                <div class="text-3xl font-bold text-white mb-6">
                    ₺<?php echo number_format($currentBalance, 2); ?>
                </div>
                
<div class="balance-message mb-6">
    Bakiye yüklemek istiyorsanız lütfen satıcı ile iletişime geçin.
</div>
<a href="https://discord.gg/your-discord-invite" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561 19.9 19.9 0 006.0347 3.0748.0777.0777 0 00.0842-.0276c.4646-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057 13.0933 13.0933 0 01-1.872-.8884.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.119.099.246.1982.3727.2924a.0766.0766 0 01-.006.1276 12.84 12.84 0 01-1.873.8883.0766.0766 0 00-.0407.106c.3604.698.7688 1.3628 1.225 1.9932a.076.076 0 00.0842.0286 19.876 19.876 0 006.032-3.075.082.082 0 00.03-.0552c.5-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1824 0-2.1568-1.0857-2.1568-2.419 0-1.3332.9554-2.4189 2.1568-2.4189 1.2109 0 2.1758 1.0952 2.1569 2.419 0 1.3332-.946 2.4189-2.1569 2.4189z"/>
    </svg>
    Discord'a Katıl
</a>
            </div>

            <!-- Transaction History -->
            <div>
                <h2 class="text-2xl font-semibold text-white mb-4">Son İşlemler</h2>
                <div class="transaction-list">
                    <?php if (empty($transactions)): ?>
                        <div class="transaction-item text-center py-8">
                            <i class="fas fa-history text-4xl mb-4 text-purple-400"></i>
                            <p>Henüz hiç işlem yok.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="transaction-item">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-white">
                                            <?php echo htmlspecialchars($transaction['product_name']); ?>
                                        </h3>
                                        <p class="text-sm text-purple-300">
                                            <?php echo date('d.m.Y H:i', strtotime($transaction['created_at'])); ?>
                                        </p>
                                    </div>
                                    <span class="font-semibold text-white">
                                        -₺<?php echo number_format($transaction['price'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
