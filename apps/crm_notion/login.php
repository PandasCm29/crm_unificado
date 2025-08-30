<?php
require_once 'config/auth.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $query = "SELECT idusuario, usuario, password, area FROM usuarios WHERE usuario = :login";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':login', $login);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashAlmacenado = $row['password'];
        $hashIngresado = md5($pass);

        if ($hashIngresado === $hashAlmacenado) {
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['area'] = $row['area'];
            $_SESSION['idusuario'] = (int)$row['idusuario'];

            if ($remember) {
                setcookie('login', $login, time() + (30 * 24 * 60 * 60), "/");
                setcookie('password', $pass, time() + (30 * 24 * 60 * 60), "/");
            } else {
                setcookie('login', '', time() - 3600, "/");
                setcookie('password', '', time() - 3600, "/");
            }

            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>🐼 Login - Sistema</title>
  <link rel="stylesheet" href="assets/css/output.css?v=<?= time() ?>">
  <link rel="icon" href="icons/login.ico" type="image/x-icon" />
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
  </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4 bg-gradient-to-br from-orange-400 via-yellow-400 to-orange-400 bg-[length:200%_200%] animate-[gradientShift_4s_ease_infinite] font-[Poppins,sans-serif]">

  <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-2xl animate-fade-in-up">
    <div class="flex justify-center mb-6">
      <img src="icons/logo.png" alt="Logo" class="h-16 object-contain animate-bounce" />
    </div>
    <div class="flex justify-center mb-6">
      <img src="icons/usuario.png" alt="Usuario" class="h-[140px]" />
    </div>

    <form method="POST" action="login.php" class="space-y-6">
      <div class="flex flex-col md:flex-row gap-4">
        <div class="w-full md:w-1/2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Usuario</label>
          <input type="text" name="login" required placeholder="Usuario"
            class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-orange-500 focus:outline-none transition transform focus:scale-105" />
        </div>

        <div class="w-full md:w-1/2 relative">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
          <input type="password" id="password" name="password" required
            class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm pr-12 focus:ring-2 focus:ring-orange-500 focus:outline-none transition transform focus:scale-105" />
          <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-9 text-gray-500">
            <i id="eye-icon" class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <input type="checkbox" name="remember" class="text-orange-600 rounded" />
        <span class="text-sm text-gray-700">Recuérdame</span>
      </div>

      <div class="text-center">
        <button type="submit"
          class="w-full py-2 px-4 bg-orange-500 text-white rounded-xl hover:bg-orange-600 transition transform hover:scale-105 font-semibold shadow">
          Iniciar Sesión
        </button>
      </div>
    </form>

    <?php if (!empty($error)): ?>
    <div class="mt-6 text-center">
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">¡Error! </strong>
        <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <script>
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
</body>
</html>
