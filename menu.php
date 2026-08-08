<link rel="stylesheet" href="styles/menu.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
  :root {
    --color-pink-principal: #ff6699;
    --color-pink-hover: #ff3377;
    --color-pink-claro: #fff0f5;
    --color-pink-borde: #fbcfe8;
  }

  /* Estilos del menú en rosa pastelería */
  .navbar-pink {
    background-color: #ffffff;
    border-bottom: 2px solid var(--color-pink-principal);
    box-shadow: 0 2px 10px rgba(255, 102, 153, 0.15);
  }

  .brand-text-pink {
    color: var(--color-pink-principal);
    font-weight: 800;
    font-size: 1.3rem;
    letter-spacing: 0.5px;
    text-decoration: none;
    transition: color 0.2s ease-in-out;
  }
  .brand-text-pink:hover {
    color: var(--color-pink-hover);
  }

  .nav-link-pink {
    color: #4a4a4a !important;
    font-weight: 600;
    margin: 0 8px;
    transition: all 0.2s ease-in-out;
    border-radius: 15px;
    padding: 6px 14px !important;
  }
  .nav-link-pink:hover {
    color: var(--color-pink-principal) !important;
    background-color: var(--color-pink-claro);
  }

  /* Personalización del botón Toggler Móvil */
  .navbar-toggler-pink {
    border-color: var(--color-pink-principal);
    color: var(--color-pink-principal);
  }
  .navbar-toggler-pink:focus {
    box-shadow: 0 0 0 0.25rem rgba(255, 102, 153, 0.25);
  }

  /* Offcanvas Móvil */
  .offcanvas-header-pink {
    border-bottom: 1px solid var(--color-pink-borde);
    background-color: var(--color-pink-claro);
  }
</style>

<nav class="navbar navbar-expand-md navbar-light fixed-top navbar-pink" style="z-index:99;">
  <div class="container-fluid containernav" style="margin: 0px 45px;">
    
    <!-- Marca Principal: Pastelería & Repostería -->
    <a class="navbar-brand brand-text-pink" href="tienda-en-linea.php">
      <i class="fas fa-birthday-cake me-2"></i>SWEET DELIGHTS 🍰
    </a>

    <button class="navbar-toggler navbar-toggler-pink" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header offcanvas-header-pink">
        <span class="offcanvas-title brand-text-pink" id="offcanvasNavbarLabel">
          <i class="fas fa-birthday-cake me-2"></i>SWEET DELIGHTS 🍰
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body">
        <div class="navbar-nav ms-auto menucanvass">
          <a class="nav-item nav-link nav-link-pink" href="tienda-en-linea.php"><i class="fas fa-cookie-bite me-1"></i> Postres & Bebidas</a>
          <a class="nav-item nav-link nav-link-pink" href="login.php"><i class="fas fa-user-lock me-1"></i> Acceso</a>
          <a class="nav-item nav-link nav-link-pink" href="carrito-de-compras.php" title="Ver mi pedido"><i class="fas fa-shopping-basket fs-5"></i></a>
        </div>
      </div>
    </div>

  </div>
</nav>

<script src="script/menu.js"></script>