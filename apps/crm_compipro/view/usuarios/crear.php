<?php
require_once __DIR__ . '/../../config/auth.php';
requireTIArea();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <link href="<?php echo $base?>assets/css/static/font-awesome.6.5.0.all.min.css" defer rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo $base ?>assets/css/usuarios.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        input,
        textarea,
        select,
        button {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <link rel="stylesheet" href="<?php echo $base?>assets/css/output.css" refer>
</head>

<body>
    <div class="container">
        <a href="<?php echo $base?>index.php" class="flex items-center px-4 py-2 rounded-lg text-white font-medium shadow-md
            transition duration-300 ease-in-out hover:brightness-110 hover:-translate-y-[2px]"
            style="background-color: rgba(0, 0, 0, 0.85); position: absolute; right: 20px; top: 5px;">
               <i class="fas fa-sign-out-alt mr-2 text-lg"></i>
            Regresar
        </a>
        <div class="header">
            <h1>CREAR USUARIO</h1>
        </div>
        <form id="form-usuario" data-mode="Creado">
            <div class="form-container">
                <div class="section">
                    <h2 class="section-title">DATOS PERSONALES</h2>
                    <div class="form-group">
                        <label for="nombres">Ingrese los nombres</label>
                        <input type="text" id="nombres" name="nombres" required>
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Ingrese los apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" required>
                    </div>
                    <div class="form-group">
                        <label for="dni">Ingrese el DNI</label>
                        <input type="tel" maxlength="8" id="dni" name="dni" class="border-gray-300 focus:ring-primary" required>
                        <small class="mensaje-validacion text-gray-500 text-xs block mt-1"></small>
                    </div>
                    <div class="form-group">
                        <label for="celular">Ingrese el celular</label>
                        <input type="tel" id="celular" name="celular" placeholder="923612546" class="border-gray-300 focus:ring-primary" required>
                        <small class="mensaje-validacion text-gray-500 text-xs block mt-1"></small>
                    </div>
                    <div class="form-group">
                        <label for="correo">Ingrese el correo</label>
                        <input type="email" id="correo" placeholder="usuario@compina.net" name="correo" class="border-gray-300 focus:ring-primary" required>
                        <small class="mensaje-validacion text-gray-500 text-xs block mt-1"></small>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="section">
                    <h2 class="section-title">DATOS DE JORNADA</h2>
                    <div class="form-group">
                        <label for="usuario">Ingrese el usuario o autogenérelo</label>
                        <div class="input-with-button">
                            <input type="text" id="usuario" name="usuario" required>
                            <button type="button" class="auto-generate-btn" onclick="generarUsuario()" title="Generar correo automáticamente">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Ingrese la contraseña</label>
                        <div class="input-with-button">
                            <div class="password-container">
                                <input type="password" id="password" name="password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword()" title="Mostrar/Ocultar contraseña">
                                    <i class="fas fa-eye" id="eye-icon"></i>
                                </button>
                            </div>
                            <button type="button" class="auto-generate-btn" onclick="generarPassword()" title="Generar contraseña automáticamente">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipoempleado">Ingrese el tipo de usuario</label>
                        <select id="tipoempleado" name="tipoempleado" required>
                            <option value="">Seleccione...</option>
                            <option value="operario">Operario</option>
                            <option value="lider">Líder</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="input-area">
                            Ingrese el área
                            <span id="areas-legend" class="ml-2"></span>
                        </label>
                        <input type="text" id="input-area" name="area" class="form-control" required placeholder="Escribe el área">
                    </div>
                    <div class="form-group-hour">
                        <label for="hora_entrada">Ingrese la hora de entrada y de salida</label>
                        <input type="time" id="hora_entrada" name="hora_entrada" title="Hora de entrada">
                        <input type="time" id="hora_salida" name="hora_salida" title="Hora de salida">
                    </div>
                </div>
                <button type="submit" class="submit-btn flex items-center justify-center">
                    <span class="button-text">Crear Usuario</span>
                    <span class="spinner hidden ml-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8h8a8 8 0 01-8 8 8 8 0 01-8-8z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
    <script src="<?php echo $base?>assets/js/static/sweetalert2.all.min.js" defer></script>

    <script type="module" src="<?php echo $base ?>assets/js/usuarios/formulario.js"></script>
    <script>
        function generarUsuario() {
            const nombres = document.getElementById("nombres").value.trim().toLowerCase();
            const apellidos = document.getElementById("apellidos").value.trim().toLowerCase();
            if (!nombres || !apellidos) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Por favor, ingrese los nombres y apellidos para generar el usuario.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            const nombreSeparado = nombres.split(" ");
            const apellidoSeparado = apellidos.split(" ");
            const inicialNombre = nombreSeparado[0].charAt(0);
            const primerApellido = apellidoSeparado[0];
            const inicialSegundoApellido = apellidoSeparado.length > 1 ? apellidoSeparado[1].charAt(0) : "";

            const usuario = (inicialNombre + primerApellido + inicialSegundoApellido).toLowerCase();
            document.getElementById("usuario").value = usuario;
        }

        function generarPassword() {
            const nombres = document.getElementById("nombres").value.trim().toLowerCase();
            const apellidos = document.getElementById("apellidos").value.trim().toLowerCase();
            if (!nombres || !apellidos) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Por favor, ingrese los nombres y apellidos para generar la contraseña.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }


            const nombreSeparado = nombres.split(" ");
            const primerNombre = nombreSeparado[0];
            const primerApellido = apellidos.split(" ")[0];

            const password = (primerNombre + primerApellido + "25").toLowerCase();

            document.getElementById("password").value = password;
        }

        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>

</body>

</html>