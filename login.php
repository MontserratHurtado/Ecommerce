<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbcon.php';

// Reutilizamos el sistema de alertas SweetAlert2 que ya tienes en usuarios.php
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
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $password = $_POST['password'];

    // Acceso especial directo para pruebas
    if (($username === 'montserrat' || strtolower($username) === 'montserrat') && $password === '12345') {
        $_SESSION['username'] = 'montserrat';
        $_SESSION['usuario_id'] = 1;
        
        header("Location: tienda-en-linea.php");
        exit(0);
    }

    // Consulta SQL buscando por usuario o correo (si la columna email no existe, busca por username)
    $query = "SELECT * FROM usuarios WHERE (username = '$username') AND estatus = '1' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    // Verificación segura: nos aseguramos de que mysqli_query no haya devuelto false
    if ($query_run && mysqli_num_rows($query_run) > 0) {
        $row = mysqli_fetch_assoc($query_run);

        if (password_verify($password, $row['password'])) {
            // Guardar la sesión
            $_SESSION['username'] = $row['username'];
            $_SESSION['usuario_id'] = $row['id'];
            
            // Redirección a la tienda en línea
            header("Location: tienda-en-linea.php");
            exit(0);
        } else {
            $_SESSION['alert'] = [
                'title' => 'CONTRASEÑA INCORRECTA',
                'message' => 'Por favor, verifica tus datos.',
                'icon' => 'error'
            ];
            header("Location: login.php");
            exit(0);
        }
    } else {
        $_SESSION['alert'] = [
            'title' => 'USUARIO NO ENCONTRADO',
            'message' => 'El usuario no está registrado, está inactivo o hubo un detalle en la BD.',
            'icon' => 'error'
        ];
        header("Location: login.php");
        exit(0);
    }
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

        /* Notita discreta para el profesor */
        .note-profesor {
            background-color: var(--color-pink-claro);
            border: 1px dashed var(--color-pink-borde);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.85rem;
            color: #666;
        }
    </style>
</head>
<body>

<div class="card card-login">
    <div class="text-center mb-3">
        <i class="bi bi-cake2-fill text-pink" style="font-size: 2.5rem;"></i>
        <h3 class="fw-bold text-pink mt-2">Iniciar Sesión</h3>
    </div>
    
    <form action="login.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Usuario / Correo</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Ingresa tu usuario" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="********" required>
        </div>
        <div class="d-grid gap-2 mb-3">
            <button type="submit" name="login_btn" class="btn btn-pink">Ingresar 🧁</button>
        </div>
    </form>

    <!-- Notita discreta para el profesor -->
    <div class="note-profesor text-center mt-2">
        <i class="bi bi-info-circle-fill text-pink me-1"></i>
        <span><b>Acceso de prueba:</b> Usuario: <code class="text-pink">montserrat</code> | Clave: <code class="text-pink">12345</code></span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</body>
</html>