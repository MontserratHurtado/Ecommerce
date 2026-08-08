<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require 'dbcon.php';

$alert = $_SESSION['alert'] ?? null;

if (!empty($alert)) {
    $title = json_encode($alert['title'] ?? 'Notificación');
    $message = json_encode($alert['message'] ?? '');
    $icon = json_encode($alert['icon'] ?? 'info');

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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: tienda-en-linea.php');
    exit;
}

$stmt = $con->prepare("SELECT * FROM pedidos WHERE identificador = ? LIMIT 1");
if (!$stmt) { die($con->error); }

$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$resultado = $stmt->get_result();

if (!$resultado || $resultado->num_rows === 0) {
    header('Location: tienda-en-linea.php');
    exit;
}

$pedido = $resultado->fetch_assoc();

if (isset($pedido['status_pago']) && strtolower($pedido['status_pago']) === 'pagado') {
    header('Location: tienda-en-linea.php');
    exit;
}

$ventas = [];
$stmtVentas = $con->prepare("SELECT titulo, sku, cantidad, precio, descuento FROM ventas WHERE identificador = ?");
if (!$stmtVentas) { die($con->error); }

$stmtVentas->bind_param('s', $pedido['identificador']);
$stmtVentas->execute();
$resVentas = $stmtVentas->get_result();

while ($row = $resVentas->fetch_assoc()) {
    $ventas[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="sidenav.css">
    <title>Pasarela de Pago | Sweet Delights 🍰</title>
    
    <!-- Carga CSS Personalizado -->
    <link rel="stylesheet" href="estilo-global.css?v=<?php echo time(); ?>">

    <style>
        :root {
            --color-pink-principal: #ff6699;
            --color-pink-hover: #ff3377;
            --color-pink-claro: #fff0f5;
            --color-pink-borde: #fbcfe8;
        }

        body {
            color: #4a4a4a;
            background-color: #fffafc;
        }

        /* Utilidades Rosa */
        .text-pink {
            color: var(--color-pink-principal) !important;
        }

        .bg-pink-light {
            background-color: var(--color-pink-claro) !important;
            border: 1px solid var(--color-pink-borde) !important;
        }

        /* Botones */
        .btn-pink {
            background-color: var(--color-pink-principal);
            color: #ffffff;
            border: 1px solid var(--color-pink-principal);
            font-weight: 600;
            border-radius: 20px;
        }
        .btn-pink:hover, .btn-pink:focus {
            background-color: var(--color-pink-hover);
            color: #ffffff;
            border-color: var(--color-pink-hover);
        }

        /* Contenedores de tarjeta y formulario */
        .card-pink-border {
            border: 1px solid var(--color-pink-borde);
        }

        /* Personalización Radios e Inputs */
        .form-check-input:checked {
            background-color: var(--color-pink-principal);
            border-color: var(--color-pink-principal);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-pink-principal);
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 153, 0.25);
        }
    </style>
    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://openpay.s3.amazonaws.com/openpay.v1.min.js"></script>
    <script type="text/javascript" src="https://openpay.s3.amazonaws.com/openpay-data.v1.min.js"></script>

    <script type="text/javascript">
        const OPENPAY_ID = "<?= $_ENV['OPENPAY_ID'] ?? '' ?>";
        const OPENPAY_PK = "<?= $_ENV['OPENPAY_PK'] ?? '' ?>";

        $(document).ready(function() {
            OpenPay.setId(OPENPAY_ID);
            OpenPay.setApiKey(OPENPAY_PK);
            OpenPay.setSandboxMode(true); // Cambiar a false en Producción

            // Generar Device Session ID de Openpay
            var deviceSessionId = OpenPay.deviceData.setup("payment-form", "deviceIdHiddenFieldName");
            $("#deviceIdHiddenFieldName").val(deviceSessionId);

            $('#pay-button').on('click', function(event) {
                event.preventDefault();
                const method = $('input[name="payment_method"]:checked').val();

                $(this).prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...');

                if (method === 'card') {
                    OpenPay.token.extractFormAndCreate('payment-form', successCallback, errorCallback);
                } else {
                    $('#payment-form').submit();
                }
            });

            var successCallback = function(response) {
                var token_id = response.data.id;
                $('#token_id').val(token_id);
                $('#payment-form').submit();
            };

            var errorCallback = function(response) {
                var desc = response.data && response.data.description ? response.data.description : (response.message || "Error al procesar la tarjeta");
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Pago',
                    text: desc,
                    confirmButtonColor: '#ff6699'
                });

                $("#pay-button").prop("disabled", false).text("PAGAR $<?= number_format($pedido['total'], 2); ?>");
            };
        });
    </script>
</head>

<body>
    <?php 
    if (file_exists('componentes/menu.php')) { include 'componentes/menu.php'; } 
    elseif (file_exists('menu.php')) { include 'menu.php'; }
    ?>

    <div class="container-fluid py-4" style="margin-top: 80px;">
        <div class="row justify-content-center">

            <!-- RESUMEN DEL PEDIDO -->
            <div class="col-11 col-md-4 mt-3 p-4 category_list order-2 order-md-1 bg-white rounded-4 shadow-sm card-pink-border">
                <h4 class="fw-bold mb-3 text-pink">🍰 Resumen de tu pedido dulce</h4>
                <p class="mb-1"><b>ID Pedido:</b> <span class="small text-muted"><?= htmlspecialchars($pedido['identificador']); ?></span></p>

                <hr>
                <p class="mb-1"><b>Cliente:</b> <?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellidop']); ?></p>
                <p class="small text-muted mb-1"><?= htmlspecialchars($pedido['email']); ?></p>
                <p class="small text-muted"><?= htmlspecialchars($pedido['telefono']); ?></p>

                <p class="mb-1"><b>Dirección de Entrega:</b></p>
                <p class="small text-muted"><?= htmlspecialchars($pedido['calle']); ?> #<?= htmlspecialchars($pedido['exterior']); ?>, <?= htmlspecialchars($pedido['colonia']); ?>, <?= htmlspecialchars($pedido['ciudad']); ?>. CP <?= htmlspecialchars($pedido['postal']); ?></p>

                <hr>
                <p class="fw-bold text-pink">Tus Postres y Pasteles:</p>

                <?php foreach ($ventas as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="fw-bold small"><?= (int)$item['cantidad'] ?>x <?= htmlspecialchars($item['titulo']) ?></span>
                        </div>
                        <div class="text-end">
                            <span class="small fw-bold text-pink">$<?= number_format($item['cantidad'] * $item['precio'], 2) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <hr>
                <div class="d-flex justify-content-between mb-1">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($pedido['subtotal'], 2); ?></span>
                </div>
                <?php if ($pedido['cuponMonto'] > 0): ?>
                    <div class="d-flex justify-content-between mb-1 text-pink">
                        <span>Cupón:</span>
                        <span>-$<?= number_format($pedido['cuponMonto'], 2); ?></span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-1">
                    <span>Envío:</span>
                    <span><?= $pedido['envioMonto'] > 0 ? '$' . number_format($pedido['envioMonto'], 2) : 'GRATIS'; ?></span>
                </div>
                <div class="d-flex justify-content-between fs-5 fw-bold mt-2 text-pink">
                    <span>Total:</span>
                    <span>$<?= number_format($pedido['total'], 2); ?></span>
                </div>
            </div>

            <!-- FORMULARIO DE PAGO -->
            <div class="col-11 col-md-6 mt-3 p-4 bg-white rounded-4 shadow-sm order-1 order-md-2 me-md-3 card-pink-border">
                <h2 class="fw-bold mb-4 text-pink">PASO 3: PAGO</h2>

                <form action="codepago.php" method="POST" id="payment-form">
                    <input type="hidden" name="identificador" value="<?= htmlspecialchars($pedido['identificador']); ?>">
                    <input type="hidden" name="token_id" id="token_id">
                    <input type="hidden" name="use_card_points" id="use_card_points" value="false">
                    <input type="hidden" name="deviceIdHiddenFieldName" id="deviceIdHiddenFieldName">
                    <input type="hidden" name="update" id="update" value="step">

                    <p class="fw-bold mb-2">Elige tu método de pago</p>
                    <div class="d-flex gap-4 mb-4 p-3 bg-pink-light rounded-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="tarjetaRadio" value="card" checked>
                            <label class="form-check-label fw-semibold" for="tarjetaRadio">Tarjeta de Débito / Crédito</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="transferenciaRadio" value="bank_account">
                            <label class="form-check-label fw-semibold" for="transferenciaRadio">Transferencia SPEI</label>
                        </div>
                    </div>

                    <!-- DATOS DE LA TARJETA -->
                    <div class="containerTarjeta row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nombre del Titular</label>
                            <input type="text" class="form-control" placeholder="Como aparece en la tarjeta" data-openpay-card="holder_name">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Número de Tarjeta</label>
                            <input type="text" class="form-control" placeholder="0000 0000 0000 0000" data-openpay-card="card_number" maxlength="16">
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-semibold">Mes Expiración</label>
                            <select id="expMonth" class="form-select" data-openpay-card="expiration_month">
                                <option value="01">01 - Enero</option>
                                <option value="02">02 - Febrero</option>
                                <option value="03">03 - Marzo</option>
                                <option value="04">04 - Abril</option>
                                <option value="05">05 - Mayo</option>
                                <option value="06">06 - Junio</option>
                                <option value="07">07 - Julio</option>
                                <option value="08">08 - Agosto</option>
                                <option value="09">09 - Septiembre</option>
                                <option value="10">10 - Octubre</option>
                                <option value="11">11 - Noviembre</option>
                                <option value="12">12 - Diciembre</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-semibold">Año Expiración</label>
                            <select id="expYear" class="form-select" data-openpay-card="expiration_year">
                                <?php
                                $anioActual = (int)date('Y');
                                for ($i = 0; $i <= 10; $i++) {
                                    $year2Digits = substr((string)($anioActual + $i), -2);
                                    echo "<option value='$year2Digits'>" . ($anioActual + $i) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-semibold">Código CVV</label>
                            <input type="text" class="form-control" placeholder="3 o 4 dígitos" data-openpay-card="cvv2" maxlength="4">
                        </div>
                    </div>

                    <button type="button" class="btn btn-pink w-100 mt-4 py-2 fs-5" id="pay-button">
                        PAGAR $<?= number_format($pedido['total'], 2); ?>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <small class="text-muted"><i class="bi bi-shield-lock-fill text-pink"></i> Tus datos están protegidos con encriptación de 256 bits mediante Openpay / BBVA.</small>
                </div>
            </div>

        </div>
    </div>

    <?php if (file_exists('footer.php')) { include 'footer.php'; } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('input[name="payment_method"]').on('change', function() {
                if ($(this).val() === 'bank_account') {
                    $('.containerTarjeta').slideUp();
                    $('#pay-button').prop('disabled', false).text('GENERAR FICHA SPEI');
                } else {
                    $('.containerTarjeta').slideDown();
                    $('#pay-button').text('PAGAR $<?= number_format($pedido['total'], 2); ?>');
                }
            });
        });
    </script>
</body>
</html>