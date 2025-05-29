<?php
require_once '../config.php';
requireLogin();

// Get all products
$stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$balance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    // Get product price
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $price = $stmt->fetch(PDO::FETCH_ASSOC)['price'];
    
    if ($balance >= $price) {
        // Start transaction
        $conn->beginTransaction();
        try {
            // Update user balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$price, $_SESSION['user_id']]);
            
            // Create purchase record
            $stmt = $conn->prepare("INSERT INTO purchases (user_id, product_id, price, status) VALUES (?, ?, ?, 'completed')");
            $stmt->execute([$_SESSION['user_id'], $product_id, $price]);
            
            $conn->commit();
            $success = "Satın alma işlemi başarılı!";
            
            // Refresh balance
            $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $balance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "İşlem sırasında bir hata oluştu.";
        }
    } else {
        $error = "Yetersiz bakiye!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler</title>
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

        .product-card {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(138, 63, 242, 0.2);
        }

        .buy-button {
            background: rgba(138, 63, 242, 0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .buy-button:hover {
            background: rgba(138, 63, 242, 1);
            transform: translateY(-2px);
        }

        .buy-button:disabled {
            background: rgba(138, 63, 242, 0.4);
            cursor: not-allowed;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Ürünler</h1>
            <div class="flex items-center gap-4">
                <span class="text-lg">
                    Bakiye: <span class="font-bold text-white">₺<?php echo number_format($balance, 2); ?></span>
                </span>
                <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                    Geri Dön
                </a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-500/20 border border-green-500/30 text-green-200 px-4 py-3 rounded-lg mb-6">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="w-full h-48 object-cover rounded-lg mb-4">
                    <?php endif; ?>
                    
                    <h3 class="text-xl font-semibold text-white mb-2">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    
                    <p class="text-purple-300 mb-4">
                        <?php echo htmlspecialchars($product['description']); ?>
                    </p>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-white">
                            ₺<?php echo number_format($product['price'], 2); ?>
                        </span>
                        
                        <form method="POST" class="inline">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" 
                                    class="buy-button"
                                    <?php echo $balance < $product['price'] ? 'disabled' : ''; ?>>
                                Satın Al
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
