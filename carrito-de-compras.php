<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbcon.php';

header("Content-Type: text/html; charset=UTF-8");

$consultaEnvio = $con->query("SELECT valoruno, valordos FROM configuraciones WHERE nombre='Envio' LIMIT 1");
$env = $consultaEnvio ? $consultaEnvio->fetch_assoc() : [];

$envioMinimo = (float)($env['valoruno'] ?? 0); // Mínimo para envío gratis
$envioCosto = (float)($env['valordos'] ?? 0);   // Costo de envío

$consultaComision = $con->query("SELECT valoruno FROM configuraciones WHERE id=4 LIMIT 1");
$com = $consultaComision ? $consultaComision->fetch_assoc() : [];
$comisionValor = str_replace('%', '', $com['valoruno'] ?? '0');
$comisionFactor = (float)$comisionValor / 100; 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" type="image/x-icon" href="images/ics.ico">
    <title>Carrito de Compras | Sweet Delights 🍰</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="styles/sidenav.css">
    
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

        .btn-outline-pink {
            color: var(--color-pink-principal);
            border-color: var(--color-pink-principal);
            background-color: transparent;
            font-weight: bold;
            border-radius: 20px;
        }
        .btn-outline-pink:hover {
            background-color: var(--color-pink-principal);
            color: #ffffff;
        }

        /* Tarjeta de Producto */
        .card-product-pink {
            border: 1px solid var(--color-pink-borde);
            transition: all 0.2s ease-in-out;
            border-radius: 12px;
            overflow: hidden;
        }
        .card-product-pink:hover {
            border-color: var(--color-pink-principal);
            box-shadow: 0 4px 15px rgba(255, 102, 153, 0.15);
        }

        /* Imagen en el carrito */
        .cart-img-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #fff5f8;
        }

        /* Inputs */
        .form-control:focus {
            border-color: var(--color-pink-principal);
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 153, 0.25);
        }
    </style>
</head>

<body>
    <?php 
    if (file_exists('componentes/menu.php')) {
        include 'componentes/menu.php';
    } elseif (file_exists('menu.php')) {
        include 'menu.php';
    }
    ?>

    <div class="container-fluid py-4" style="margin-top: 80px;">
        <div class="row mb-5 justify-content-evenly px-2">

            <!-- RESUMEN DE COMPRA -->
            <div class="col-12 col-md-5 p-4 category_list mb-4 rounded-4 border bg-white shadow-sm">
                <h3 class="fw-bold text-uppercase text-pink">
                    <i class="bi bi-basket2-fill text-pink me-1"></i> Tu Pedido Dulce
                </h3>
                <p class="mb-1"><b>Resumen de tu compra</b></p>
                <p class="text-muted small">Total de postres: <span id="totalProductos" class="fw-bold text-dark">0</span></p>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-pink border-bottom">
                                <th>Cant.</th>
                                <th>Postre / Pastel</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="detalleCompra"></tbody>
                    </table>
                </div>

                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal:</span>
                        <span id="subtotal" class="fw-bold">$ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1" id="row-descuento" style="display:none;">
                        <span>Descuento:</span>
                        <span id="descuento" class="fw-bold text-pink">$ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1" id="row-cupon" style="display:none;">
                        <span>Cupón:</span>
                        <span id="cupon" class="fw-bold text-pink">$ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1" id="enviop">
                        <span>Costo de envío:</span>
                        <span id="envio" class="fw-bold">$ 0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-pink">
                        <span>Total a pagar:</span>
                        <span id="totalPagar">$ 0.00</span>
                    </div>
                </div>

                <a class="btn btn-pink w-100 mt-4 disabled" id="next" href="pedido.php">Continuar al Pedido 💳</a>

                <!-- CUPÓN -->
                <div class="mt-4 p-3 bg-pink-light rounded-3">
                    <label class="small fw-semibold mb-1 text-pink"><i class="bi bi-gift-fill me-1"></i> ¿Tienes un cupón de descuento?</label>
                    <div class="d-flex">
                        <input class="form-control me-2" type="text" id="codigoCupon" placeholder="Ej. DULCE10">
                        <button class="btn btn-pink" id="canje">Canjear</button>
                    </div>
                </div>

                <!-- NOTIFICACIONES DE ENVÍO -->
                <div class="p-3 mt-3 text-dark rounded-3 bg-pink-light" id="envioCosto">
                    <small><i class="bi bi-truck me-1 text-pink"></i> Para <b>envío gratis</b> a domicilio se requiere un pedido mínimo de <b>$<?= number_format($envioMinimo, 2) ?></b>.</small>
                </div>
                <div class="p-3 mt-3 text-dark rounded-3 bg-pink-light" id="envioGratis" style="display:none;">
                    <small><i class="bi bi-heart-fill me-1 text-pink"></i> ¡Genial! Tu envío a domicilio es totalmente <b>GRATIS</b>.</small>
                </div>
            </div>

            <!-- LISTADO DE PRODUCTOS (CON IMÁGENES) -->
            <div class="col-12 col-md-6">
                <div class="row justify-content-start" id="productList">
                    <div class="text-center py-5">
                        <div class="spinner-border text-pink" role="status"></div>
                        <p class="mt-2 text-muted">Cargando los postres de tu carrito...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php 
    if (file_exists('footer.php')) { include 'footer.php'; } 
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let cuponDescuento = 0;
        let cuponeando = false;
        let cuponCargadoInicial = false;
        const ENVIO_MINIMO = <?= $envioMinimo ?>;
        const ENVIO_COSTO = <?= $envioCosto ?>;
        const COMISION_FACTOR = <?= $comisionFactor ?>;
        const btnNext = document.getElementById("next");

        function getCart() {
            try {
                return JSON.parse(localStorage.getItem("empresaCart")) || [];
            } catch (e) {
                return [];
            }
        }

        function saveCart(cart) {
            localStorage.setItem("empresaCart", JSON.stringify(cart));
        }

        function changeQuantity(id, change) {
            let cart = getCart();
            let existing = cart.find(item => String(item.id) === String(id));

            if (existing) {
                existing.cantidad += change;
                if (existing.cantidad <= 0) {
                    cart = cart.filter(item => String(item.id) !== String(id));
                    const card = document.getElementById(`card-${id}`);
                    if (card) card.remove();
                }
                saveCart(cart);
            }
            
            updateQuantityDisplay(id);
            updateTotals();
            checkCart();
        }

        function updateQuantityDisplay(id) {
            const cart = getCart();
            const item = cart.find(i => String(i.id) === String(id));
            const qtySpan = document.getElementById(`qty-${id}`);
            if (qtySpan) qtySpan.textContent = item ? item.cantidad : 0;
        }

        function updateTotals() {
            const cart = getCart();
            let totalProductos = 0;
            let subtotalConComision = 0;
            let totalDescuentoBase = 0;
            let detalle = "";

            cart.forEach(item => {
                totalProductos += item.cantidad;
                const elPrice = document.getElementById(`price-${item.id}`);
                if (!elPrice) return;

                const pUnitario = parseFloat(elPrice.dataset.precio || 0);
                const pMayoreo = parseFloat(elPrice.dataset.mayoreo || 0);
                const minMayoreo = parseInt(elPrice.dataset.minmayoreo || 0);
                const descuentoBase = parseFloat(elPrice.dataset.descuento || 0);
                const titulo = document.getElementById(`title-${item.id}`)?.textContent || "Postre";

                let aplicaMayoreo = (minMayoreo > 0 && item.cantidad >= minMayoreo && pMayoreo > 0);
                let precioBaseSeleccionado = aplicaMayoreo ? pMayoreo : pUnitario;
                let descuentoAplicable = aplicaMayoreo ? 0 : descuentoBase;

                const precioConComision = precioBaseSeleccionado * (1 + COMISION_FACTOR);

                subtotalConComision += precioConComision * item.cantidad;
                totalDescuentoBase += descuentoAplicable * item.cantidad;

                let badgeMayoreo = aplicaMayoreo ? `<br><small class="text-pink"><b>Descuento Especial Aplicado</b></small>` : "";
                let htmlDescuento = (descuentoAplicable > 0) ? `<br><small class="text-pink">-$ ${descuentoAplicable.toFixed(2)}</small>` : "";

                elPrice.innerHTML = `Precio: $ ${precioConComision.toFixed(2)} ${badgeMayoreo} ${htmlDescuento}`;

                const totalFila = (precioConComision - descuentoAplicable) * item.cantidad;
                detalle += `
                    <tr>
                        <td class="fw-bold">${item.cantidad}</td>
                        <td>${titulo}</td>
                        <td class="text-end fw-bold">$ ${totalFila.toFixed(2)}</td>
                    </tr>`;
            });

            let montoParaEnvio = subtotalConComision - totalDescuentoBase - cuponDescuento;
            let costoEnvioAplicado = 0;

            if (montoParaEnvio < ENVIO_MINIMO && cart.length > 0) {
                costoEnvioAplicado = ENVIO_COSTO;
                document.getElementById("envioCosto").style.display = "block";
                document.getElementById("envioGratis").style.display = "none";
                document.getElementById("enviop").style.display = "flex";
                document.getElementById("envio").textContent = `$ ${ENVIO_COSTO.toFixed(2)}`;
            } else {
                costoEnvioAplicado = 0;
                document.getElementById("envioCosto").style.display = "none";
                document.getElementById("envioGratis").style.display = (cart.length > 0) ? "block" : "none";
                document.getElementById("enviop").style.display = "none";
            }

            let descuentoTotalSumado = totalDescuentoBase + cuponDescuento;
            let totalFinal = Math.max(0, subtotalConComision - descuentoTotalSumado + costoEnvioAplicado);

            document.getElementById("row-descuento").style.display = (totalDescuentoBase > 0) ? "flex" : "none";
            document.getElementById("row-cupon").style.display = (cuponDescuento > 0) ? "flex" : "none";

            document.getElementById("detalleCompra").innerHTML = detalle || "<tr><td colspan='3' class='text-center text-muted'>Tu carrito está vacío 🍰</td></tr>";
            document.getElementById("totalProductos").textContent = totalProductos;
            document.getElementById("subtotal").textContent = `$ ${subtotalConComision.toFixed(2)}`;
            document.getElementById("descuento").textContent = `-$ ${totalDescuentoBase.toFixed(2)}`;
            document.getElementById("cupon").textContent = `-$ ${cuponDescuento.toFixed(2)}`;
            document.getElementById("totalPagar").textContent = `$ ${totalFinal.toFixed(2)}`;

            localStorage.setItem("empresaSubtotal", subtotalConComision.toFixed(2));
            localStorage.setItem("empresaDescuento", descuentoTotalSumado.toFixed(2));
            localStorage.setItem("empresaEnvio", costoEnvioAplicado.toFixed(2));
            localStorage.setItem("empresaTotal", totalFinal.toFixed(2));
        }

        function checkCart() {
            let cart = getCart();
            if (!cart || cart.length === 0) {
                btnNext.classList.add("disabled");
                btnNext.style.pointerEvents = "none";
            } else {
                btnNext.classList.remove("disabled");
                btnNext.style.pointerEvents = "auto";
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const cart = getCart();
            const ids = cart.map(item => item.id);

            if (ids.length === 0) {
                document.getElementById("productList").innerHTML = `
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                        <p class="fs-5 text-muted">Tu carrito de postres está vacío 🍰</p>
                        <a href="tienda-en-linea.php" class="btn btn-pink">Ver Menú de Pasteles</a>
                    </div>`;
                updateTotals();
                checkCart();
                return;
            }

            $.ajax({
                url: "get_cart_products.php",
                type: "POST",
                data: { ids: ids },
                dataType: "json"
            }).done(function(data) {
                if (!data || data.length === 0) {
                    $("#productList").html("<p class='text-center text-pink fw-bold'>No se encontraron postres disponibles.</p>");
                    saveCart([]);
                    updateTotals();
                    checkCart();
                    return;
                }

                let html = "";
                data.forEach(prod => {
                    let imgPath = prod.imagen ? prod.imagen : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=200';
                    html += `
                    <div class="col-12 mb-3" id="card-${prod.productoID}">
                        <div class="card card-product-pink rounded-3 bg-white">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="${imgPath}" alt="${prod.titulo}" class="cart-img-thumb me-3" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=200';">
                                    <div class="flex-grow-1">
                                        <h5 id="title-${prod.productoID}" class="card-title mb-1 fw-bold text-pink fs-6">${prod.titulo}</h5>
                                        <p id="price-${prod.productoID}" 
                                           class="mb-0 small text-muted"
                                           data-precio="${prod.preciounitario}" 
                                           data-mayoreo="${prod.preciomayoreo}" 
                                           data-minmayoreo="${prod.cantidadmayoreo}"
                                           data-descuento="${prod.descuento}">
                                           Cargando precio...
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-end border-top pt-2 mt-2">
                                    <span class="me-3 small text-muted">Cantidad:</span>
                                    <button class="btn btn-sm btn-outline-pink" onclick="changeQuantity('${prod.productoID}', -1)">−</button>
                                    <span id="qty-${prod.productoID}" class="mx-3 fw-bold text-pink fs-5">0</span>
                                    <button class="btn btn-sm btn-outline-pink" onclick="changeQuantity('${prod.productoID}', 1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });

                $("#productList").html(html);

                cart.forEach(item => updateQuantityDisplay(item.id));
                updateTotals();
                checkCart();

            }).fail(function() {
                $("#productList").html(`
                    <div class="alert alert-warning text-center">
                        Hubo un problema al cargar los detalles de los postres.
                    </div>`);
            });
        });

        // Evento de Canjear Cupón
        $("#canje").on("click", function(e) {
            e.preventDefault();
            let codigo = $("#codigoCupon").val().trim().toUpperCase();
            if (codigo === "") {
                Swal.fire("Atención", "Escribe un código de cupón", "warning");
                return;
            }

            let subtotal = parseFloat($("#subtotal").text().replace("$", "").trim());

            $.post("validar_cupon.php", { codigo: codigo, subtotal: subtotal }, function(res) {
                if (res.ok) {
                    cuponDescuento = res.descuento;
                    localStorage.setItem("empresaCupon", codigo);
                    updateTotals();
                    Swal.fire("¡Éxito!", `Cupón aplicado: -$${res.descuento.toFixed(2)}`, "success");
                } else {
                    Swal.fire("Cupón Inválido", res.msg || "El cupón no existe o expiró", "error");
                }
            }, "json");
        });
    </script>
</body>
</html>