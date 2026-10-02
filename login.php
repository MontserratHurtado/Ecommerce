<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbcon.php';

// Configuración de protección contra Fuerza Bruta
$max_intentos = 3;
$tiempo_bloqueo = 300; // 5 minutos en segundos

// Verificar si el usuario está actualmente bloqueado
if (isset($_SESSION['lockout_time'])) {
    $tiempo_transcurrido = time() - $_SESSION['lockout_time'];
    if ($tiempo_transcurrido < $tiempo_bloqueo) {
        $tiempo_restante = ceil(($tiempo_bloqueo - $tiempo_transcurrido) / 60);
        $_SESSION['alert'] = [
            'title' => 'ACCESO BLOQUEADO',
            'message' => "Demasiados intentos fallidos. Por seguridad, intente de nuevo en {$tiempo_restante} minuto(s).",
            'icon' => 'warning'
        ];
    } else {
        // Desbloquear si ya transcurrieron los 5 minutos
        unset($_SESSION['lockout_time']);
        unset($_SESSION['login_attempts']);
    }
}

// Reutilizamos el sistema de alertas SweetAlert2
$alert = isset($_SESSION['alert']) ? $_SESSION['alert'] : null;

if (!empty($alert)) {
    $title = isset($alert['title']) ? json_encode($alert['title']) : '"Notificación"';
    $message = isset($alert['message']) ? json_encode($alert['message']) : '""';
    $icon = isset($alert['icon']) ? json_encode($alert['icon']) : '"info"';

    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: $title,
                    " . (!empty($alert['message']) ? "text: $message," : "") . "
                    icon: $icon,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ff6699'
                });
            });
        </script>";
    unset($_SESSION['alert']);
}

// Procesar el formulario cuando se envía por POST
if (isset($_POST['login_btn'])) {

    // Comprobar si el usuario está bloqueado antes de procesar
    if (isset($_SESSION['lockout_time']) && (time() - $_SESSION['lockout_time'] < $tiempo_bloqueo)) {
        header("Location: login.php");
        exit(0);
    }

    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $password = $_POST['password'];

    // Acceso especial directo para pruebas (Asignando Rol Administrador)
    if (($username === 'montserrat' || strtolower($username) === 'montserrat') && $password === '12345') {
        
        // 1. REGENERAR ID DE SESIÓN (Prevención de Session Hijacking)
        session_regenerate_id(true);

        // 2. REINICIAR CONTADOR DE INTENTOS
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);

        // 3. ASIGNAR VARIABLES DE SESIÓN Y ROL (RBAC)
        $_SESSION['username'] = 'montserrat';
        $_SESSION['usuario_id'] = 1;
        $_SESSION['rol'] = 'Admin'; // Rol Administrador asignado
        
        header("Location: tienda-en-linea.php");
        exit(0);
    }

    // Consulta SQL buscando por usuario
    $query = "SELECT * FROM usuarios WHERE (username = '$username') AND estatus = '1' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    if ($query_run && mysqli_num_rows($query_run) > 0) {
        $row = mysqli_fetch_assoc($query_run);

        // Verificación con algoritmo seguro BCRYPT
        if (password_verify($password, $row['password'])) {
            
            // 1. REGENERAR ID DE SESIÓN (Fase 3.3 - Seguridad de Sesión)
            session_regenerate_id(true);

            // 2. REINICIAR CONTADOR DE INTENTOS FALLIDOS
            unset($_SESSION['login_attempts']);
            unset($_SESSION['lockout_time']);

            // 3. GUARDAR SESIÓN Y ROL (Fase 3.3 - RBAC: Admin vs Vendedor)
            $_SESSION['username'] = $row['username'];
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['rol'] = isset($row['rol']) ? $row['rol'] : 'Vendedor'; // Asignación de Rol
            
            header("Location: tienda-en-linea.php");
            exit(0);
        } else {
            // Manejo de intento fallido por contraseña errónea
            registrar_intento_fallido($max_intentos, $tiempo_bloqueo);
        }
    } else {
        // Manejo de intento fallido por usuario no encontrado
        registrar_intento_fallido($max_intentos, $tiempo_bloqueo);
    }
}

// Función auxiliar para registrar intentos fallidos y activar bloqueo
function registrar_intento_fallido($max_intentos, $tiempo_bloqueo) {
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

    if ($_SESSION['login_attempts'] >= $max_intentos) {
        $_SESSION['lockout_time'] = time();
        $_SESSION['alert'] = [
            'title' => 'CUENTA BLOQUEADA TEMPORALMENTE',
            'message' => 'Has superado los 3 intentos fallidos. Tu acceso ha sido bloqueado por 5 minutos.',
            'icon' => 'error'
        ];
    } else {
        $restantes = $max_intentos - $_SESSION['login_attempts'];
        $_SESSION['alert'] = [
            'title' => 'DATOS INCORRECTOS',
            'message' => "Usuario o contraseña no válidos. Te quedan {$restantes} intento(s) antes del bloqueo.",
            'icon' => 'error'
        ];
    }
    header("Location: login.php");
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Pastelería & Repostería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --color-pink-principal: #ff6699;
            --color-pink-hover: #ff3377;
            --color-pink-claro: #fff0f5;
            --color-pink-borde: #fbcfe8;
        }

        body { 
            background-color: #fffafc; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh;
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .card-login { 
            width: 100%; 
            max-width: 400px; 
            padding: 30px; 
            border-radius: 15px; 
            border: 1px solid var(--color-pink-borde);
            box-shadow: 0px 8px 20px rgba(255, 102, 153, 0.15); 
            background-color: #ffffff;
        }

        .text-pink {
            color: var(--color-pink-principal) !important;
        }

        .btn-pink {
            background-color: var(--color-pink-principal);
            color: #ffffff;
            border: 1px solid var(--color-pink-principal);
            font-weight: 600;
            border-radius: 20px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .btn-pink:hover {
            background-color: var(--color-pink-hover);
            color: #ffffff;
            border-color: var(--color-pink-hover);
        }

        .form-control:focus {
            border-color: var(--color-pink-principal);
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 153, 0.25);
        }

        .form-label {
            color: #555555;
            font-weight: 500;
        }

        .note-profesor {
            background-color: var(--color-pink-claro);
            border: 1px dashed var(--color-pink-borde);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.85rem;
            color: #666;
        }

        .password-hint {
            font-size: 0.75rem;
            color: #888;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<div class="card card-login">
    <div class="text-center mb-3">
        <i class="bi bi-cake2-fill text-pink" style="font-size: 2.5rem;"></i>
        <h3 class="fw-bold text-pink mt-2">Iniciar Sesión</h3>
    </div>
    
    <form action="login.php" method="POST" id="loginForm">
        <div class="mb-3">
            <label for="username" class="form-label">Usuario / Correo</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Ingresa tu usuario" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="********" required>
            <div class="password-hint" id="passwordHint">
                <i class="bi bi-shield-lock"></i> La contraseña debe tener mín. 8 caracteres, mayúscula, minúscula y número.
            </div>
        </div>
        <div class="d-grid gap-2 mb-3">
            <button type="submit" name="login_btn" class="btn btn-pink">Ingresar 🧁</button>
        </div>
    </form>

    <!-- Notita para el profesor -->
    <div class="note-profesor text-center mt-2">
        <i class="bi bi-info-circle-fill text-pink me-1"></i>
        <span><b>Acceso de prueba:</b> Usuario: <code class="text-pink">montserrat</code> | Clave: <code class="text-pink">12345</code></span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<!-- Validación JS de Complejidad de Contraseña (Fase 3.3) -->
<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const username = document.getElementById('username').value;

    // Excepción de prueba para el usuario montserrat
    if (username.toLowerCase() === 'montserrat') {
        return true;
    }

    // Regla: Mínimo 8 caracteres, al menos 1 mayúscula, 1 minúscula y 1 número
    const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

    if (!strongPasswordRegex.test(password)) {
        e.preventDefault();
        Swal.fire({
            title: 'CONTRASEÑA DÉBIL',
            text: 'Por lineamientos de seguridad, la contraseña debe incluir al menos 8 caracteres, una letra mayúscula, una minúscula y un número.',
            icon: 'warning',
            confirmButtonColor: '#ff6699'
        });
    }
});
</script>
</body>
</html>