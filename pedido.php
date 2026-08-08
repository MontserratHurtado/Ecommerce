<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbcon.php';

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
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Acción al confirmar la alerta
                    }
                });
            });
        </script>";
    unset($_SESSION['alert']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/sidenav.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/ico.ico" />
    <title>Información de Envío | Sweet Delights 🍰</title>

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

        /* Inputs */
        .form-control:focus {
            border-color: var(--color-pink-principal);
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 153, 0.25);
        }

        .card-pink-border {
            border: 1px solid var(--color-pink-borde);
        }
    </style>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSynovc&libraries=places&callback=initMap" async></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let cart = localStorage.getItem("empresaCart");
            if (!cart || cart === "[]" || cart.trim() === "") {
                window.location.href = "tienda-en-linea.php";
            }
            try {
                let parsed = JSON.parse(cart);
                if (!Array.isArray(parsed) || parsed.length === 0) {
                    window.location.href = "tienda-en-linea.php";
                }
            } catch (e) {
                window.location.href = "tienda-en-linea.php";
            }
        });

        let autocompleteInstances = {};

        function initMap() {
            const addressInputs = document.querySelectorAll('input[name="calle"]');
            const postalCodeInput = document.getElementById('postal');

            addressInputs.forEach(input => {
                if (!autocompleteInstances[input.name]) {
                    autocompleteInstances[input.name] = new google.maps.places.Autocomplete(input, {
                        fields: ["place_id", "address_components"],
                        componentRestrictions: {
                            country: ["mx"]
                        }
                    });

                    autocompleteInstances[input.name].addListener("place_changed", () => {
                        const place = autocompleteInstances[input.name].getPlace();
                        handlePlaceChange(place);
                    });
                }
            });
        }

        function handlePlaceChange(place) {
            const postalCodeInput = document.getElementById('postal');

            if (!place.address_components) {
                document.querySelectorAll("[required]").forEach(i => i.value = "");
                return;
            }

            place.address_components.forEach(component => {
                const type = component.types[0];
                const longName = component.long_name;

                switch (type) {
                    case "street_number":
                        setValue("exterior", longName);
                        break;
                    case "route":
                        setValue("calle", longName);
                        break;
                    case "sublocality":
                    case "neighborhood":
                    case "political":
                        setValue("colonia", longName);
                        break;
                    case "locality":
                        setValue("ciudad", longName);
                        break;
                    case "administrative_area_level_1":
                        setValue("estado", longName);
                        break;
                    case "country":
                        setValue("pais", longName);
                        break;
                    case "postal_code":
                        postalCodeInput.value = longName;
                        postalCodeInput.dataset.touched = "true";
                        validarCampo(postalCodeInput);
                        break;
                }
            });

            validarFormulario();
        }
    </script>
</head>

<body>
    <?php 
    if (file_exists('componentes/menu.php')) { include 'componentes/menu.php'; } 
    elseif (file_exists('menu.php')) { include 'menu.php'; }
    ?>

    <div class="container-fluid" style="margin-top: 80px;">
        <div class="row py-4 justify-content-center">
            <div class="col-12 col-md-8 bg-white p-4 p-md-5 rounded-4 shadow-sm card-pink-border">
                <h2 class="fw-bold text-pink mb-4">PASO 2: INFORMACIÓN PARA ENTREGA DE TU PEDIDO</h2>
                <form action="codepago.php" method="post" class="row">
                    <input type="hidden" name="cuponLS" id="cuponLS">
                    <input type="hidden" name="cartLS" id="cartLS">
                    <input type="hidden" name="subtotalLS" id="subtotalLS">
                    <input type="hidden" name="descuentoLS" id="descuentoLS">
                    <input type="hidden" name="envioLS" id="envioLS">
                    <input type="hidden" name="totalLS" id="totalLS">

                    <div class="form-floating col-12">
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre" autocomplete="off" required maxlength="50">
                        <label for="nombre">Nombre</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="form-floating col-12 col-md-6 mt-3">
                        <input type="text" class="form-control" name="apellidop" id="apellidop" placeholder="Apellido paterno" autocomplete="off" required maxlength="35">
                        <label for="apellidop">Apellido paterno</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="form-floating col-12 col-md-6 mt-3">
                        <input type="text" class="form-control" name="apellidom" id="apellidom" placeholder="Apellido materno" autocomplete="off" required maxlength="35">
                        <label for="apellidom">Apellido materno</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="form-floating col-12 col-md-8 mt-3">
                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" autocomplete="off" required maxlength="80">
                        <label for="email">Correo electrónico</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="form-floating col-12 col-md-4 mt-3">
                        <input type="text" class="form-control" name="telefono" id="telefono" placeholder="Telefono" autocomplete="off" required minlength="10" maxlength="10">
                        <label for="telefono">Teléfono (10 dígitos)</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 mt-3 mb-2">
                        <p class="small text-muted"><i class="bi bi-info-circle text-pink me-1"></i> Asegúrate de ingresar correctamente tu correo electrónico, ya que ahí recibirás la confirmación de tus postres y el estado de tu entrega.</p>
                    </div>

                    <div class="col-12 col-md-8 form-floating mb-3">
                        <input type="text" class="form-control" name="calle" placeholder="Calle" autocomplete="off" required maxlength="50">
                        <label for="calle">Calle</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 col-md-4 form-floating mb-3">
                        <input type="text" class="form-control" name="exterior" placeholder="Exterior" autocomplete="off" required maxlength="10">
                        <label for="exterior">Número exterior</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 col-md-4 form-floating mb-3">
                        <input type="text" class="form-control" name="interior" placeholder="Interior" autocomplete="off" maxlength="10">
                        <label for="interior">Número interior (Opcional)</label>
                    </div>

                    <div class="col-12 col-md-8 form-floating mb-3">
                        <input type="text" class="form-control" name="colonia" placeholder="Colonia" autocomplete="off" required maxlength="50">
                        <label for="colonia">Colonia / Fraccionamiento</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 col-md-6 form-floating mb-3">
                        <input type="text" class="form-control" name="ciudad" placeholder="Ciudad" autocomplete="off" required maxlength="50">
                        <label for="ciudad">Ciudad / Municipio</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 col-md-6 form-floating mb-3">
                        <input type="text" class="form-control" name="estado" placeholder="Estado" autocomplete="off" required maxlength="50">
                        <label for="estado">Estado</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 col-md-7 form-floating mb-3">
                        <input type="text" class="form-control" name="postal" id="postal" placeholder="Postal" autocomplete="off" required maxlength="20">
                        <label for="postal">Código postal</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 col-md-5 form-floating mb-3">
                        <input type="text" class="form-control" name="pais" placeholder="Pais" autocomplete="off" required maxlength="50">
                        <label for="pais">País</label>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <button class="btn btn-pink fs-5 w-100 py-2" id="btnGuardar" name="save" type="submit" disabled>Ir a pagar 💳</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (file_exists('footer.php')) { include 'footer.php'; } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>
    <script>
        function setValue(name, value) {
            const input = document.querySelector(`input[name="${name}"]`);
            if (!input) return;

            input.value = value;
            input.dataset.touched = "true";
            validarCampo(input);
        }

        function validarFormulario() {
            const form = document.querySelector("form");
            const requiredFields = form.querySelectorAll("[required]");
            const btnGuardar = document.getElementById("btnGuardar");
            let allFilled = true;
            requiredFields.forEach(field => {
                if (!field.value.trim()) allFilled = false;
            });

            const cartLS = JSON.parse(localStorage.getItem("empresaCart") || "[]");
            const cartNotEmpty = Array.isArray(cartLS) && cartLS.length > 0;

            btnGuardar.disabled = !(allFilled && cartNotEmpty);
        }

        document.querySelectorAll("[required]").forEach(input => {
            input.addEventListener("focus", () => {
                input.dataset.touched = "true";
            });

            input.addEventListener("blur", () => {
                validarCampo(input);
                validarFormulario();
            });

            input.addEventListener("input", () => {
                validarCampo(input);
                validarFormulario();
            });
        });

        function validarCampo(input) {
            if (!input.dataset.touched) return;

            const value = input.value.trim();
            let valido = true;
            let mensaje = "Este campo es obligatorio";

            if (!value) {
                valido = false;
            }

            if (valido && input.type === "email") {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    valido = false;
                    mensaje = "Ingresa un correo electrónico válido";
                }
            }

            if (valido && input.name === "telefono") {
                if (!/^\d{10}$/.test(value)) {
                    valido = false;
                    mensaje = "El teléfono debe tener 10 dígitos";
                }
            }

            const feedback = input.parentElement.querySelector(".invalid-feedback");
            if (!valido) {
                input.classList.add("is-invalid");
                input.classList.remove("is-valid");
                if (feedback) feedback.textContent = mensaje;
            } else {
                input.classList.remove("is-invalid");
                input.classList.add("is-valid");
            }
        }

        window.addEventListener("storage", validarFormulario);

        function cargarDatosLocalStorage() {
            document.getElementById("cuponLS").value = localStorage.getItem("empresaCupon") || "";
            document.getElementById("cartLS").value = localStorage.getItem("empresaCart") || "[]";
            document.getElementById("subtotalLS").value = localStorage.getItem("empresaSubtotal") || "0";
            document.getElementById("descuentoLS").value = localStorage.getItem("empresaDescuento") || "0";
            document.getElementById("envioLS").value = localStorage.getItem("empresaEnvio") || "200";
            document.getElementById("totalLS").value = localStorage.getItem("empresaTotal") || "0";
        }

        document.querySelector("form").addEventListener("submit", function(e) {
            cargarDatosLocalStorage();
        });

        document.addEventListener("DOMContentLoaded", function() {
            validarFormulario();
            cargarDatosLocalStorage();
        });

        document.getElementById("telefono").addEventListener("input", function() {
            this.value = this.value.replace(/\D/g, "");
        });
    </script>
</body>

</html>