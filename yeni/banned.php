<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesabınız Yasaklı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1a0527;
            min-height: 100vh;
            color: #c9a9ff;
            position: relative;
            overflow-x: hidden;
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

        .banned-card {
            background: rgba(63, 26, 122, 0.2);
            border: 1px solid rgba(138, 63, 242, 0.3);
            border-radius: 1rem;
            padding: 2rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="aurora-container">
        <div class="aurora aurora-1"></div>
        <div class="aurora aurora-2"></div>
    </div>

    <div class="banned-card max-w-md w-full text-center relative z-10">
        <div class="text-6xl mb-6">🚫</div>
        <h1 class="text-3xl font-bold text-white mb-4">Hesabınız Yasaklandı</h1>
        <p class="text-purple-300 mb-6">
            Üzgünüz, hesabınıza erişim yasaklanmıştır. Daha fazla bilgi için lütfen satıcı ile iletişime geçin.
        </p>
        <a href="giris.php" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition">
            <span>Giriş Sayfasına Dön</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>
</body>
</html>
