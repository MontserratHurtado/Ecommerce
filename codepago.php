<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Cargar Autoload de Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// 2. Cargar SDK de Openpay
if (file_exists(__DIR__ . '/openpay/OpenpayApi.php')) {
    require_once __DIR__ . '/openpay/OpenpayApi.php';
}
if (file_exists(__DIR__ . '/openpay/Openpay.php')) {
    require_once __DIR__ . '/openpay/Openpay.php';
}

// 3. Cargar variables .env
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'dbcon.php';

// ELIMINAR PEDIDO
if (isset($_POST['delete'])) {
    $registro_id = mysqli_real_escape_string($con, $_POST['delete']);
    $query = "DELETE FROM pedidos WHERE id='$registro_id'";
    mysqli_query($con, $query);
    header("Location: industrias.php");
    exit(0);
}

// PROCESAR PAGO
if (isset($_POST['update'])) {

    if (!isset($_POST['identificador']) || empty($_POST['identificador'])) {
        die('Identificador no recibido');
    }

    $identificador = $_POST['identificador'];

    $stmt = $con->prepare("SELECT nombre, apellidop, apellidom, email, telefono, total FROM pedidos WHERE identificador = ? LIMIT 1");
    if (!$stmt) { die($con->error); }

    $stmt->bind_param("s", $identificador);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if (!$resultado || $resultado->num_rows === 0) {
        die('Pedido no encontrado');
    }

    $pedido = $resultado->fetch_assoc();
    $stmt->close();

    $openpay_id = $_ENV['OPENPAY_ID'] ?? $_ENV['OPENPAY_MERCHANT_ID'] ?? getenv('OPENPAY_ID') ?? '';
    $openpay_sk = $_ENV['OPENPAY_SK'] ?? $_ENV['OPENPAY_PRIVATE_KEY'] ?? getenv('OPENPAY_SK') ?? '';
    $openpay_country = $_ENV['OPENPAY_COUNTRY'] ?? 'MX';

    $openpay = null;

    if (class_exists('\Openpay\Data\Openpay')) {
        $openpay = \Openpay\Data\Openpay::getInstance($openpay_id, $openpay_sk, $openpay_country, $_SERVER['REMOTE_ADDR']);
        \Openpay\Data\Openpay::setProductionMode(false);
    } elseif (class_exists('Openpay')) {
        $openpay = Openpay::getInstance($openpay_id, $openpay_sk, $openpay_country, $_SERVER['REMOTE_ADDR']);
        Openpay::setProductionMode(false);
    } else {
        die('Error: No se pudo inicializar la clase de Openpay.');
    }

    $customer = [
        'name'         => $pedido['nombre'],
        'last_name'    => trim($pedido['apellidop'] . ' ' . $pedido['apellidom']),
        'phone_number' => $pedido['telefono'],
        'email'        => $pedido['email'],
    ];

    $method = $_POST['payment_method'] ?? 'card';
    $montoFinal = number_format((float)$pedido['total'], 2, '.', '');

    try {
        if ($method === 'card') {
            $chargeData = array(
                'method'            => 'card',
                'source_id'         => $_POST["token_id"] ?? '',
                'amount'            => $montoFinal,
                'description'       => 'Pedido de pastelería y postres #' . $identificador,
                'order_id'          => $identificador . '_' . time(),
                'device_session_id' => $_POST["deviceIdHiddenFieldName"] ?? '',
                'customer'          => $customer
            );
        } else {
            $chargeData = array(
                'method'      => 'bank_account',
                'amount'      => $montoFinal,
                'description' => 'Pedido de pastelería y postres #' . $identificador,
                'order_id'    => $identificador . '_' . time(),
                'customer'    => $customer
            );
        }

        $charge = $openpay->charges->create($chargeData);

        if ($method === 'bank_account') {
            $vigencia   = $charge->due_date;
            $bank       = $charge->payment_method->bank;
            $clabe      = $charge->payment_method->clabe;
            $convenio   = $charge->payment_method->agreement;
            $referencia = $charge->payment_method->name;
            $url_pdf    = $charge->payment_method->url_spei;

            $fechaObj = new DateTime($vigencia);
            $formateador = new IntlDateFormatter(
                'es_ES',
                IntlDateFormatter::LONG,
                IntlDateFormatter::SHORT,
                'America/Mexico_City',
                IntlDateFormatter::GREGORIAN
            );

            $vigenciaAmigable = $formateador->format($fechaObj);

            $update_stmt = $con->prepare("UPDATE pedidos SET 
                status_pago = 'Pendiente SPEI', 
                openpay_id = ?, 
                pdf_url = ?, 
                clabe = ?,
                vigencia = ?,
                banco = ?,
                convenio = ?,
                referencia = ? 
                WHERE identificador = ?");

            $update_stmt->bind_param("ssssssss", $charge->id, $url_pdf, $clabe, $vigenciaAmigable, $bank, $convenio, $referencia, $identificador);
            $update_stmt->execute();

            header("Location: orden.php?id=" . $identificador);
            exit();
        } else {
            if ($charge->status == 'completed') {
                $update_stmt = $con->prepare("UPDATE pedidos SET status_pago = 'Pagado', openpay_id = ? WHERE identificador = ?");
                $update_stmt->bind_param("ss", $charge->id, $identificador);
                $update_stmt->execute();

                header("Location: orden.php?id=" . $identificador);
                exit();
            } else if ($charge->status == 'charge_pending') {
                $redirectUrl = ($method === 'bank_account') ? $charge->payment_method->url_spei : $charge->payment_method->url;
                header("Location: " . $redirectUrl);
                exit();
            }
        }
    } catch (\Exception $e) {
        handleOpenpayError($e, $identificador);
    }

    exit(0);
}

function handleOpenpayError($e, $identificador)
{
    $errorCode = method_exists($e, 'getErrorCode') ? $e->getErrorCode() : 0;

    switch ($errorCode) {
        case 3001: case 3004: case 3005: $message = 'La tarjeta fue rechazada.'; break;
        case 3002: $message = 'La tarjeta ha expirado.'; break;
        case 3003: $message = 'Fondos insuficientes.'; break;
        case 2005: $message = 'La fecha de expiración es incorrecta.'; break;
        case 15001: $message = 'La autenticación de la tarjeta falló.'; break;
        default: $message = 'Error en el pago (' . $errorCode . '): ' . $e->getMessage(); break;
    }

    $_SESSION['alert'] = [
        'title'   => 'PAGO NO APROBADO',
        'message' => $message,
        'icon'    => 'error'
    ];

    header("Location: pago.php?id=$identificador");
    exit(0);
}

// GUARDAR NUEVO PEDIDO
if (isset($_POST['save'])) {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellidop = trim($_POST['apellidop'] ?? '');
    $apellidom = trim($_POST['apellidom'] ?? '');
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $telefono  = trim($_POST['telefono'] ?? '');
    $calle     = trim($_POST['calle'] ?? '');
    $exterior  = trim($_POST['exterior'] ?? '');
    $interior  = trim($_POST['interior'] ?? '');
    $colonia   = trim($_POST['colonia'] ?? '');
    $ciudad    = trim($_POST['ciudad'] ?? '');
    $estado    = trim($_POST['estado'] ?? '');
    $postal    = trim($_POST['postal'] ?? '');
    $pais      = trim($_POST['pais'] ?? '');
    $cupon     = trim($_POST['cuponLS'] ?? '');
    $productos = $_POST['cartLS'] ?? '';
    $estatus   = 1;

    $envio     = floatval($_POST['envioLS'] ?? $_POST['envio'] ?? 0);
    $descuento = floatval($_POST['descuentoLS'] ?? $_POST['descuento'] ?? 0);

    $cartArray = json_decode($productos, true) ?? [];
    $subtotal = floatval($_POST['subtotalLS'] ?? $_POST['subtotal'] ?? 0);
    $itemsToInsert = [];

    if (is_array($cartArray)) {
        $subtotalCalculado = 0;
        foreach ($cartArray as $item) {
            $prod_id = intval($item['id'] ?? $item['idproducto'] ?? 0);
            $cant    = intval($item['cantidad'] ?? $item['quantity'] ?? 1);
            $precio  = 0;

            if ($prod_id > 0) {
                $p_stmt = $con->prepare("SELECT preciounitario FROM productosventa WHERE id = ? LIMIT 1");
                if ($p_stmt) {
                    $p_stmt->bind_param("i", $prod_id);
                    $p_stmt->execute();
                    $p_stmt->bind_result($db_precio);
                    if ($p_stmt->fetch()) { $precio = floatval($db_precio); }
                    $p_stmt->close();
                }
            }

            if ($precio == 0) {
                $precio = floatval($item['precio'] ?? $item['preciounitario'] ?? 0);
            }

            $subtotalCalculado += ($precio * $cant);

            if ($prod_id > 0) {
                $itemsToInsert[] = [
                    'idproducto' => $prod_id,
                    'cantidad'   => $cant,
                    'precio'     => $precio
                ];
            }
        }

        if ($subtotal <= 0) { $subtotal = $subtotalCalculado; }
    }

    $total = max(0, $subtotal - $descuento + $envio);

    $sql = "INSERT INTO pedidos 
            (nombre, apellidop, apellidom, email, telefono, calle, exterior, interior, colonia, ciudad, estado, postal, pais, cupon, cuponMonto, descuentoTotal, subtotal, envioMonto, total, productos, estatus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssssssi",
        $nombre, $apellidop, $apellidom, $email, $telefono,
        $calle, $exterior, $interior, $colonia, $ciudad,
        $estado, $postal, $pais, $cupon, $descuento,
        $descuento, $subtotal, $envio, $total,
        $productos, $estatus
    );

    if ($stmt->execute()) {
        $last_id = $con->insert_id;
        $stmt->close();

        $folio_num = str_pad($last_id, 7, "0", STR_PAD_LEFT);
        $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellidop, 0, 1) . substr($apellidom, 0, 1));
        $identificador = "SWEETDELIGHTS-$folio_num-$iniciales";

        $up_stmt = $con->prepare("UPDATE pedidos SET identificador=? WHERE id=?");
        $up_stmt->bind_param("si", $identificador, $last_id);
        $up_stmt->execute();
        $up_stmt->close();

        if (!empty($itemsToInsert)) {
            $pp_stmt = $con->prepare("INSERT INTO productospedidos (idproducto, identificador, cantidad, precio, surtido, estatus) VALUES (?, ?, ?, ?, 0, 1)");
            if ($pp_stmt) {
                foreach ($itemsToInsert as $pi) {
                    $pp_stmt->bind_param("isid", $pi['idproducto'], $identificador, $pi['cantidad'], $pi['precio']);
                    $pp_stmt->execute();
                }
                $pp_stmt->close();
            }
        }

        header("Location: pago.php?id=$identificador");
        exit(0);
    } else {
        header("Location: pedido.php");
        exit(0);
    }
}