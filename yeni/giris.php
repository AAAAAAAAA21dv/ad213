<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Tüm alanları doldurunuz.';
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Redirect based on user type
            if ($user['is_admin']) {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit;
        } else {
            $error = 'Geçersiz e-posta veya şifre.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1a0527;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c9a9ff;
            position: relative;
            overflow: hidden;
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
        }

        .aurora-1 {
            background: linear-gradient(45deg, #ff00ff, #00ffff);
            opacity: 0.3;
            animation: aurora-movement 15s ease infinite;
        }

        .aurora-2 {
            background: linear-gradient(-45deg, #ff00ff, #00ffff);
            opacity: 0.2;
            animation: aurora-movement 20s ease infinite reverse;
        }

        .aurora-3 {
            background: linear-gradient(90deg, #ff00ff, #00ffff);
            opacity: 0.1;
            animation: aurora-movement 25s ease infinite;
        }

        @keyframes aurora-movement {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            50% { transform: translate(-30%, -30%) rotate(180deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .login-container {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            animation: fade-in-up 0.8s ease forwards;
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px;
            padding-left: 40px;
            background: rgba(63, 26, 122, 0.3);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: rgba(138, 63, 242, 0.8);
            box-shadow: 0 0 15px rgba(138, 63, 242, 0.3);
        }

        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(138, 63, 242, 0.8);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: rgba(138, 63, 242, 0.8);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: rgba(138, 63, 242, 1);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(138, 63, 242, 0.3);
        }

        .error-message {
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="aurora-container">
        <div class="aurora aurora-1"></div>
        <div class="aurora aurora-2"></div>
        <div class="aurora aurora-3"></div>
    </div>

    <div class="login-container">
        <h1 class="text-3xl font-bold text-center mb-8 text-white">Giriş Yap</h1>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="E-posta adresiniz" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Şifreniz" required>
            </div>

            <button type="submit" class="submit-btn">
                Giriş Yap
            </button>
        </form>

        <p class="text-center mt-6 text-sm">
            Hesabınız yok mu?
            <a href="kayit.php" class="text-purple-400 hover:text-purple-300 font-semibold">
                Kayıt Ol
            </a>
        </p>
    </div>
</body>
</html>
