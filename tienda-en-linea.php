<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbcon.php';

header("Content-Type: text/html; charset=UTF-8");

// Redirigir a login si no hay sesión activa
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SWEET DELIGHTS 🍰 | Pasteles, Postres & Repostería Fina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" href="styles/sidenav.css">
    <link rel="stylesheet" href="styles/slickslider.css">
    <link rel="stylesheet" href="estilo-global.css?v=3.0">
    
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

        .btn-pink {
            background-color: var(--color-pink-principal);
            color: #ffffff;
            border: 1px solid var(--color-pink-principal);
            font-weight: 600;
            border-radius: 20px;
        }
        .btn-pink:hover {
            background-color: var(--color-pink-hover);
            color: #ffffff;
            border-color: var(--color-pink-hover);
        }

        .btn-outline-pink {
            color: var(--color-pink-principal);
            border-color: var(--color-pink-principal);
            background-color: transparent;
            border-radius: 20px;
        }
        .btn-outline-pink:hover {
            background-color: var(--color-pink-principal);
            color: #ffffff;
        }

        .text-pink {
            color: var(--color-pink-principal) !important;
        }

        /* Tarjeta de Producto */
        .card-product-pink {
            border: 1px solid var(--color-pink-borde);
            transition: all 0.3s ease-in-out;
            border-radius: 15px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .card-product-pink:hover {
            border-color: var(--color-pink-principal);
            box-shadow: 0 8px 20px rgba(255, 102, 153, 0.2);
            transform: translateY(-3px);
        }

        .product-img-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            background-color: #fff5f8;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .card-product-pink:hover .product-img-container img {
            transform: scale(1.05);
        }

        .promo-banner-pink {
            background-color: var(--color-pink-claro);
            border: 1px solid var(--color-pink-borde);
            border-left: 5px solid var(--color-pink-principal);
            padding: 15px 20px;
            border-radius: 12px;
        }

        .floating-button a {
            background-color: var(--color-pink-principal) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(255, 102, 153, 0.4);
            padding: 12px 22px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
        }
        .floating-button a:hover {
            background-color: var(--color-pink-hover) !important;
        }

        .form-control:focus {
            border-color: var(--color-pink-principal);
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 153, 0.25);
        }
    </style>
</head>

<body>
    <?php 
    if (file_exists('menu.php')) {
        include 'menu.php'; 
    }
    ?>
    
    <div class="container-fluid">
        <!-- Control de Sesión Superior -->
        <div class="row pt-3 px-3 justify-content-end" style="margin-top: 70px;">
            <div class="col-auto">
                <a href="login.php" class="btn btn-pink px-4 shadow-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                </a>
            </div>
        </div>

        <div class="row mb-5 mt-2 justify-content-start" style="padding:0px 10px;">
            <!-- Promociones / Slider -->
            <?php
            $query_promo = "SELECT * FROM promociones WHERE estatus = 1 ORDER BY id DESC";
            $query_run_promo = mysqli_query($con, $query_promo);

            if ($query_run_promo && mysqli_num_rows($query_run_promo) > 0) {
            ?>
                <div class="col-12 p-0 mb-3">
                    <div class="slickcard">
                        <?php foreach ($query_run_promo as $registro_promo): ?>
                            <div class="slickimg p-2" data-aos="zoom-in">
                                <a style="width: 100%; text-decoration: none;" href="<?= htmlspecialchars($registro_promo['url'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="promo-banner-pink d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cake2-fill text-pink fs-3 me-3"></i>
                                            <div>
                                                <h5 class="m-0 fw-bold text-pink"><?= htmlspecialchars($registro_promo['titulo'] ?? '¡Promoción Dulce del Día! 🎂', ENT_QUOTES, 'UTF-8'); ?></h5>
                                                <small class="text-muted"><?= htmlspecialchars($registro_promo['descripcion'] ?? '¡Disfruta de nuestros descuentos en pasteles y postres!', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                        </div>
                                        <span class="btn btn-sm btn-pink">Ver promoción</span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php
            }
            ?>

            <!-- Menú Lateral de Filtros Original -->
            <div class="col-12 col-md-2 category_list">
                <div class="form-floating mt-1 mb-3">
                    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Buscar postre o pastel...">
                    <label style="padding-left: 0px;" for="searchInput">Buscar pastel o postre...</label>
                </div>

                <p class="mt-0 mb-0"><small class="fw-bold text-pink"><i class="bi bi-heart-fill me-1"></i> Especialidades:</small></p>
                <label class="d-block">
                    <input type="checkbox" name="all" class="all_item" value="all">
                    Todas las especialidades
                </label>
                <?php
                $query_ind = "SELECT * FROM industrias ORDER BY id DESC";
                $query_run_ind = mysqli_query($con, $query_ind);
                if ($query_run_ind && mysqli_num_rows($query_run_ind) > 0) {
                    foreach ($query_run_ind as $registro) {
                ?>
                        <label class="d-block">
                            <input type="checkbox" name="industry[]" class="industry_item" value="<?= htmlspecialchars($registro['industria'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($registro['industria'], ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                <?php
                    }
                }
                ?>

                <p class="mt-3 mb-1"><small class="fw-bold text-pink"><i class="bi bi-shop me-1"></i> Categorías:</small></p>
                <?php
                $query_cat = "SELECT * FROM categorias ORDER BY id DESC";
                $query_run_cat = mysqli_query($con, $query_cat);
                if ($query_cat && mysqli_num_rows($query_run_cat) > 0) {
                    foreach ($query_run_cat as $registro) {
                ?>
                        <label class="d-block">
                            <input type="checkbox" name="category[]" class="category_item" value="<?= htmlspecialchars($registro['categoria'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($registro['categoria'], ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                <?php
                    }
                }
                ?>
            </div>

            <!-- Listado de Productos (FILTRADO EXCLUSIVO PARA POSTRES Y MEDIOSVENTA) -->
            <div class="col-12 col-md-10 card-contain">
                <div class="row justify-content-start" id="productList">
                    <?php
                    $comision_porcentaje = 0;
                    $query_config = "SELECT valoruno FROM configuraciones WHERE id = 4 LIMIT 1";
                    $result_config = mysqli_query($con, $query_config);

                    if ($result_config && $row_config = mysqli_fetch_assoc($result_config)) {
                        $valor_limpio = str_replace('%', '', $row_config['valoruno']);
                        $comision_porcentaje = (float)$valor_limpio / 100;
                    }

                    // Consulta SQL que filtra solo los postres e incluye las URLs de mediosventa
                    $query = "SELECT p.id AS productoID, p.titulo, p.subtitulo, p.preciounitario, p.descuento, p.preciomayoreo, p.cantidadmayoreo, mv.medio AS imagen_url,
                               GROUP_CONCAT(DISTINCT c.categoria ORDER BY c.categoria ASC SEPARATOR ', ') AS categorias,
                               GROUP_CONCAT(DISTINCT s.subcategoria ORDER BY s.subcategoria ASC SEPARATOR ', ') AS subcategorias,
                               GROUP_CONCAT(DISTINCT i.industria ORDER BY i.industria ASC SEPARATOR ', ') AS industrias
                               FROM productosventa p
                               LEFT JOIN mediosventa mv ON p.id = mv.idproducto
                               LEFT JOIN categoriasasociadasventa c ON p.id = c.idproducto
                               LEFT JOIN subcategoriasasociadasventa s ON p.id = s.idproducto
                               LEFT JOIN industriaasociadaventa i ON p.id = i.idproducto
                               WHERE (p.id >= 101 OR p.titulo LIKE '%Rol%' OR p.titulo LIKE '%Matcha%' OR p.titulo LIKE '%Café%' OR p.titulo LIKE '%Cappuccino%' OR p.titulo LIKE '%Cheesecake%' OR p.titulo LIKE '%Macarons%' OR p.titulo LIKE '%Pastel%')
                                 AND p.titulo NOT LIKE '%Escape%' 
                                 AND p.titulo NOT LIKE '%Casco%' 
                                 AND p.titulo NOT LIKE '%Freno%' 
                                 AND p.titulo NOT LIKE '%Kit%' 
                                 AND p.titulo NOT LIKE '%Ninja%'
                               GROUP BY p.id
                               ORDER BY p.id ASC";

                    $query_run = mysqli_query($con, $query);
                    if ($query_run && mysqli_num_rows($query_run) > 0) {
                        foreach ($query_run as $registro) {
                            $precio_con_descuento_base = $registro['preciounitario'] - $registro['descuento'];
                            $precio_final_con_comision = $precio_con_descuento_base * (1 + $comision_porcentaje);
                            $precio_original_con_comision = $registro['preciounitario'] * (1 + $comision_porcentaje);
                            
                            // Imagen predeterminada si no hay URL en la BD
                            $imgFallback = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=500';
                            $ruta_imagen = !empty($registro['imagen_url']) ? htmlspecialchars($registro['imagen_url'], ENT_QUOTES, 'UTF-8') : $imgFallback;
                    ?>
                            <div class="col-6 col-md-3 product-item d-flex mb-4"
                                data-unitario="<?= $registro['preciounitario']; ?>"
                                data-mayoreo="<?= $registro['preciomayoreo']; ?>"
                                data-minmayoreo="<?= $registro['cantidadmayoreo']; ?>"
                                data-comision="<?= $comision_porcentaje; ?>"
                                data-descuento="<?= $registro['descuento']; ?>"
                                id="product-card-<?= $registro['productoID']; ?>"
                                data-industry="<?= htmlspecialchars($registro['industrias'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-category="<?= htmlspecialchars($registro['categorias'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-subcategory="<?= htmlspecialchars($registro['subcategorias'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="card card-product-pink" style="width: 100%;">
                                    <a style="text-decoration: none; color: #000;" href="ver-producto.php?id=<?= $registro['productoID']; ?>">
                                        <div class="product-img-container">
                                            <img src="<?= $ruta_imagen; ?>" 
                                                 alt="<?= htmlspecialchars($registro['titulo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                 onerror="this.onerror=null; this.src='<?= $imgFallback; ?>';">
                                        </div>
                                        <div class="card-body">
                                            <div>
                                                <h5 class="card-title fw-bold text-pink fs-6 mb-1">
                                                    <?= htmlspecialchars($registro['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                                </h5>
                                                <p class="card-text text-muted small"><?= htmlspecialchars($registro['subtitulo'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <p class="mb-1 mt-2">
                                                <span id="price-display-<?= $registro['productoID']; ?>" class="fw-bold fs-5 text-pink">
                                                    $<?= number_format($precio_final_con_comision, 2); ?>
                                                </span>

                                                <span id="old-price-display-<?= $registro['productoID']; ?>"
                                                    class="text-muted"
                                                    style="text-decoration: line-through; font-size: 13px; margin-left: 8px; <?= ((float)$registro['descuento'] <= 0) ? 'display:none;' : ''; ?>">
                                                    <b>$<?= number_format($precio_original_con_comision, 2); ?></b>
                                                </span>
                                            </p>

                                            <?php if ($registro['cantidadmayoreo'] > 0 && $registro['preciomayoreo'] > 0 && $registro['preciomayoreo'] < $registro['preciounitario']): ?>
                                                <div style="font-size: 11px; color: #ff6699; font-weight: 600; margin-top: 2px;">
                                                    <i class="bi bi-bag-heart-fill"></i> Mayoreo desde <?= $registro['cantidadmayoreo'] ?> pzs.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                    
                                    <div class="d-flex align-items-center p-3 mt-auto border-top">
                                        <button onclick="addCart('<?= $registro['productoID']; ?>')"
                                            class="btn btn-pink w-100"
                                            id="btn-add-<?= $registro['productoID']; ?>">
                                            <small>
                                                <i class="bi bi-cart-plus-fill me-1"></i>
                                                <span class="add-text"> Añadir al Pedido</span>
                                            </small>
                                        </button>

                                        <div id="counter-<?= $registro['productoID']; ?>" class="ms-2 align-items-center" style="display: none;">
                                            <button class="btn btn-sm btn-outline-pink" onclick="changeQuantity('<?= $registro['productoID']; ?>', -1)">−</button>
                                            <span id="qty-<?= $registro['productoID']; ?>" class="mx-2 font-weight-bold text-pink">0</span>
                                            <button class="btn btn-sm btn-outline-pink" onclick="changeQuantity('<?= $registro['productoID']; ?>', 1)">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<div style='min-height: 70vh;display: flex;justify-content: center;align-items: center;text-align: center;'><p class='text-pink fw-bold fs-5'><i class='bi bi-cake2 me-2'></i>No hay postres disponibles por el momento.</p></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php 
    if (file_exists('footer.php')) {
        include 'footer.php'; 
    }
    ?>

    <!-- Botón Flotante del Carrito -->
    <div class="floating-button" id="cartButton" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <a href="carrito-de-compras.php">
            <span style="background-color: #ffffff; color: #ff6699; padding: 4px 8px; border-radius: 50px; margin-right: 8px;">
                <i class="bi bi-cart-check-fill"></i>
                <span id="cartCount" style="font-weight: bold; margin-left: 2px;"></span>
            </span>
            Ver Mi Pedido Dulce 🍰
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="script/slickpromo.js"></script>
    <script src="script/filtros.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const cartButton = document.getElementById("cartButton");
            const cartCount = document.getElementById("cartCount");

            function getCart() {
                return JSON.parse(localStorage.getItem("empresaCart")) || [];
            }

            function saveCart(cart) {
                localStorage.setItem("empresaCart", JSON.stringify(cart));
                window.dispatchEvent(new Event("cartUpdated"));
            }

            function actualizarBotonCarrito() {
                const cart = getCart();
                if (Array.isArray(cart) && cart.length > 0) {
                    cartButton.style.display = "block";
                    cartCount.textContent = cart.length;
                } else {
                    cartButton.style.display = "none";
                }
            }

            actualizarBotonCarrito();
            window.addEventListener("cartUpdated", actualizarBotonCarrito);
            window.addEventListener("storage", function(e) {
                if (e.key === "empresaCart") actualizarBotonCarrito();
            });

            function addCart(id) {
                let cart = getCart();
                let existing = cart.find(item => item.id === id);
                if (existing) {
                    existing.cantidad++;
                } else {
                    cart.push({ id: id, cantidad: 1 });
                }
                saveCart(cart);
                updateQuantityDisplay(id);
            }

            function changeQuantity(id, change) {
                let cart = getCart();
                let existing = cart.find(item => item.id === id);

                if (existing) {
                    existing.cantidad += change;
                    if (existing.cantidad <= 0) {
                        cart = cart.filter(item => item.id !== id);
                    }
                } else if (change > 0) {
                    cart.push({ id: id, cantidad: 1 });
                }

                saveCart(cart);
                updateQuantityDisplay(id);
            }

            function updateQuantityDisplay(id) {
                const cart = getCart();
                const item = cart.find(i => i.id === id);
                const qty = item ? item.cantidad : 0;

                const card = document.getElementById(`product-card-${id}`);
                if (!card) return;

                const pUnitario = parseFloat(card.dataset.unitario) || 0;
                const pMayoreo = parseFloat(card.dataset.mayoreo) || 0;
                const minMayoreo = parseInt(card.dataset.minmayoreo) || 0;
                const comision = parseFloat(card.dataset.comision) || 0;
                const descuento = parseFloat(card.dataset.descuento) || 0;

                const priceDisplay = document.getElementById(`price-display-${id}`);
                const oldPriceDisplay = document.getElementById(`old-price-display-${id}`);

                let tieneMayoreoValido = (minMayoreo > 0 && pMayoreo > 0 && pMayoreo < pUnitario);
                let esMayoreoActivo = (tieneMayoreoValido && qty >= minMayoreo);

                let precioBase = esMayoreoActivo ? pMayoreo : pUnitario;
                let precioFinal = (precioBase - descuento) * (1 + comision);
                let precioReferenciaOriginal = pUnitario * (1 + comision);

                if (priceDisplay) {
                    priceDisplay.textContent = `$${precioFinal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                }

                if (oldPriceDisplay) {
                    if (descuento > 0 || esMayoreoActivo) {
                        oldPriceDisplay.style.display = "inline";
                        oldPriceDisplay.innerHTML = `<b>$${precioReferenciaOriginal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</b>`;
                    } else {
                        oldPriceDisplay.style.display = "none";
                    }
                }

                const qtySpan = document.getElementById(`qty-${id}`);
                const counterDiv = document.getElementById(`counter-${id}`);
                const addBtn = document.getElementById(`btn-add-${id}`);

                if (qty > 0) {
                    if (qtySpan) qtySpan.textContent = qty;
                    if (counterDiv) counterDiv.style.display = "flex";
                    if (addBtn && addBtn.querySelector(".add-text")) addBtn.querySelector(".add-text").style.display = "none";
                } else {
                    if (counterDiv) counterDiv.style.display = "none";
                    if (addBtn && addBtn.querySelector(".add-text")) addBtn.querySelector(".add-text").style.display = "inline";
                }
            }

            getCart().forEach(item => updateQuantityDisplay(item.id));

            window.addCart = addCart;
            window.changeQuantity = changeQuantity;
        });
    </script>
</body>

</html>