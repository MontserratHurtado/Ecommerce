<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbcon.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (isset($_POST['delete'])) {
    $registro_id = mysqli_real_escape_string($con, $_POST['delete']);

    $query = "DELETE FROM usuarios WHERE id='$registro_id' ";
    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        $_SESSION['alert'] = [
            'message' => 'Usuario eliminado exitosamente',
            'title' => 'USUARIO ELIMINADO',
            'icon' => 'success'
        ];
        header("Location: usuarios.php");
        exit(0);
    } else {
        $_SESSION['alert'] = [
            'message' => 'Notifica a soporte',
            'title' => 'ERROR AL ELIMINAR',
            'icon' => 'error'
        ];
        header("Location: usuarios.php");
        exit(0);
    }
}

if (isset($_POST['update'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $apellidopaterno = mysqli_real_escape_string($con, $_POST['apellidopaterno']);
    $apellidomaterno = mysqli_real_escape_string($con, $_POST['apellidomaterno']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password']; // NO escapar todavía
    $rol = mysqli_real_escape_string($con, $_POST['rol']);
    $estatus = mysqli_real_escape_string($con, $_POST['estatus']);

    // Base del update
    $query = "
        UPDATE usuarios SET
            nombre = '$nombre',
            apellidopaterno = '$apellidopaterno',
            apellidomaterno = '$apellidomaterno',
            username = '$username',
            rol = '$rol',
            estatus = '$estatus'
    ";

    // 👉 Solo si el password NO está vacío (Fase 1.2 y 3.3)
    if (!empty($password)) {
        // 1. Validar complejidad en PHP (Backend)
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            $_SESSION['alert'] = [
                'message' => 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.',
                'title' => 'CONTRASEÑA INSEGURA',
                'icon' => 'warning'
            ];
            header("Location: usuarios.php");
            exit;
        }
        // 2. Encriptar estrictamente con BCRYPT
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query .= ", password = '$hashed_password'";
    }

    $query .= " WHERE id = '$id'";
    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        $_SESSION['alert'] = [
            'message' => 'Usuario editado exitosamente',
            'title' => 'USUARIO EDITADO',
            'icon' => 'success'
        ];
        header("Location: usuarios.php");
        exit;
    } else {
        $_SESSION['alert'] = [
            'message' => 'Notifica a soporte',
            'title' => 'ERROR AL EDITAR',
            'icon' => 'error'
        ];
        header("Location: usuarios.php");
        exit;
    }
}

if (isset($_POST['save'])) {
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $apellidopaterno = mysqli_real_escape_string($con, $_POST['apellidopaterno']);
    $apellidomaterno = mysqli_real_escape_string($con, $_POST['apellidomaterno']);
    $email = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password']; // Se procesa más abajo
    $rol = mysqli_real_escape_string($con, $_POST['rol']);
    $estatus = "1";

    if ($rol == 1) {
        $rol_nombre = "Administrador";
    } elseif ($rol == 2) {
        $rol_nombre = "Colaborador";
    } else {
        $rol_nombre = "Otro";
    }

    $check_email_query = "SELECT * FROM usuarios WHERE username='$email' LIMIT 1";
    $result = mysqli_query($con, $check_email_query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['alert'] = [
            'title' => 'ERROR',
            'message' => 'Este correo ya está registrado',
            'icon' => 'error'
        ];
        header("Location: usuarios.php");
        exit(0);
    } else {
        // 1. Validar complejidad en PHP (Backend)
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            $_SESSION['alert'] = [
                'message' => 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.',
                'title' => 'CONTRASEÑA INSEGURA',
                'icon' => 'warning'
            ];
            header("Location: usuarios.php");
            exit(0);
        }

        // 2. Encriptar estrictamente con BCRYPT antes de guardar
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO usuarios SET nombre='$nombre', apellidopaterno='$apellidopaterno', apellidomaterno='$apellidomaterno', username='$email', password='$hashed_password', rol='$rol', estatus='$estatus'";

        $query_run = mysqli_query($con, $query);
        if ($query_run) {

            // Configuracion SMTP
            $host = 'smtp.gmail.com';
            $port = 587;
            $username = 'romoccemilio@gmail.com';
            $password_smtp = 'lorspamipejixhcy'; // Cambié nombre de variable para no chocar con la del usuario
            $security = 'tls';

            $mail = new PHPMailer(true);

            // Configurar SMTP
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password_smtp;
            $mail->SMTPSecure = $security;

            // Configurar correo
            $mail->setFrom('romoccemilio@gmail.com', 'UTMA');
            $mail->addAddress($email);
            $mail->Subject = 'NUEVO USUARIO';
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);

            // Cuerpo del mensaje (Se envía la contraseña en texto plano al correo del usuario para que sepa con qué entrar)
            $cuerpo = '
                <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body style="font-family: system-ui;text-align: justify;background-color: #e7e7e7;">
                    <div style="max-width:500px;margin: 0 auto;">
                        <div style="padding: 0px 30px;padding-top: 35px;">
                            <p>Estimado/a ' . $nombre . '</p>
                            <p>Tu cuenta para gestionar el catálogo de productos y servicios de Mi Empresa se creo exitosamente.</p>
                            <p>Por seguridad no compartas tus credenciales con nadie.</p>

                            <div style="padding: 3px 20px;background-color:#efefef;color:#000000;border-radius: 3px;margin: 50px 0px;text-align:left;">
                                <p style="margin-bottom: 0px;"><b>Conoce los detalles de tu cuenta:</b></p>
                                <p><b>Correo:</b> ' . $email . '</p>
                                <p><b>Contraseña:</b> ' . $password . '</p>
                                <p><b>Rol:</b> ' . $rol_nombre . '</p>
                            </div>

                            <p style="text-align: center;margin-top:80px;margin-bottom:0px;">Atentamente</p>
                            <p style="text-align: center;margin-top:0px;margin-bottom:50px;"><b>Equipo administrativo</b></p>
                        </div>
                        <div style="background-color: #af3335;color: #ffffff;padding: 15px 15px;font-size: 10px;text-align: center;padding-bottom: 15px;margin-bottom: 25px;">
                            <p>Este correo es enviado de manera automática por nuestro sistema de respuesta rápida.</p>
                        </div>
                    </div>
                </body>
                </html>';

            $mail->Body = $cuerpo;
            $correoEnviado = false;

            try {
                $correoEnviado = $mail->send();
            } catch (Exception $e) {
                error_log('Error correo: ' . $mail->ErrorInfo);
            }

            if ($query_run && $correoEnviado) {
                $_SESSION['alert'] = [
                    'title' => 'SOLICITUD EXITOSA',
                    'message' => 'Revisa tu correo electrónico',
                    'icon' => 'success'
                ];
            } else {
                $_SESSION['alert'] = [
                    'title' => 'ERROR',
                    'message' => 'El usuario se creo pero el correo no pudo enviarse',
                    'icon' => 'warning'
                ];
            }

            header("Location: usuarios.php");
            exit(0);
        } else {
            $_SESSION['alert'] = [
                'title' => 'ERROR',
                'message' => 'Notifica a soporte',
                'icon' => 'error'
            ];
            header("Location: usuarios.php");
            exit(0);
        }
    }
}
?>