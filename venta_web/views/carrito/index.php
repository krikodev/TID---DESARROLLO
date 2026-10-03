<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Favicon -->
  <title><?= NAME_BUSINESS ?></title>
  <link rel="icon" type="image/png" href="<?= URL_IMAGEN_ADMIN ?>icono.png">

  <input type="hidden" id="url_web" value="<?= URL_WEB; ?>">
  <input type="hidden" id="url" value="<?= URL; ?>">

  <link href="<?= URL_WEB ?>lib/font-awesome-6.2.1/pro/all.css" rel="stylesheet" />

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

  <!-- calendar -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/flatpickr.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/l10n/es.js"></script>
  <script src="https://js.culqi.com/checkout-js"></script>
  <title><?= NAME_BUSINESS ?></title>
</head>

<body style="background-color: #F1F1F1;">

  <style>
    body {
      margin: 0;
      padding-top: 82px;
    }
  </style>
  <!-- Inicio de loader -->
  <div id="loader" class="d-none">
    <svg class="pl" viewBox="0 0 128 128" width="128px" height="128px" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="pl-grad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="hsl(193,90%,55%)"></stop>
          <stop offset="100%" stop-color="hsl(223,90%,55%)"></stop>
        </linearGradient>
      </defs>
      <circle class="pl__ring" r="56" cx="64" cy="64" fill="none" stroke="hsla(0,10%,10%,0.1)" stroke-width="16" stroke-linecap="round"></circle>
      <path class="pl__worm" d="M92,15.492S78.194,4.967,66.743,16.887c-17.231,17.938-28.26,96.974-28.26,96.974L119.85,59.892l-99-31.588,57.528,89.832L97.8,19.349,13.636,88.51l89.012,16.015S81.908,38.332,66.1,22.337C50.114,6.156,36,15.492,36,15.492a56,56,0,1,0,56,0Z" fill="none" stroke="url(#pl-grad)" stroke-width="16" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="44 1111" stroke-dashoffset="10"></path>
    </svg>
  </div>
  <!-- Fin de loader -->


  <!-- Inicio de buscador -->
  <div class="floating-search-wrapper" id="floating-search">
    <div class="py-3 for-principal" id="busqueda-programaciones">
      <div class="container-fluid">
        <div class="search-container">
          <form id="form-busqueda" class="row g-3 align-items-end">
            <!-- Campo Origen -->
            <div class="col-12 col-md-3">
              <div class="input-with-icon">
                <i class="fas fa-map-marker-alt input-icon"></i>
                <select class="form-control" id="origen" name="origen">
                  <?php if ($this->terminal["success"]) : ?>
                    <option value="" selected>Selecciona origen</option>
                    <?php foreach ($this->terminal['message'] as $terminal) : ?>
                      <?php $text = $terminal["nombre"] ?>
                      <option value="<?= $terminal["id_terminal"] ?>"><?= $text ?></option>
                    <?php endforeach ?>
                  <?php endif ?>
                </select>
              </div>
            </div>
            <!-- Campo Destino -->
            <div class="col-12 col-md-3">
              <div class="input-with-icon">
                <i class="fas fa-flag-checkered input-icon"></i>
                <select class="form-control" id="destino" name="destino">
                  <?php if ($this->terminal["success"]) : ?>
                    <option value="" selected>Selecciona destino</option>
                    <?php foreach ($this->terminal['message'] as $terminal) : ?>
                      <?php $text = $terminal["nombre"] ?>
                      <option value="<?= $terminal["id_terminal"] ?>"><?= $text ?></option>
                    <?php endforeach ?>
                  <?php endif ?>
                </select>
              </div>
            </div>
            <!-- Campo Fecha -->
            <div class="col-12 col-md-3">
              <div class="input-with-icon">
                <i class="fas fa-calendar-alt input-icon"></i>
                <input
                  type="date"
                  name="fecha_programacion"
                  id="fecha_programacion"
                  class="form-control"
                  value="<?= date('Y-m-d') ?>"
                  required>
              </div>
            </div>
            <!-- Botón de Búsqueda -->
            <div class="col-12 col-md-3">
              <button type="submit" class="btn btn-custom w-100">
                <i class="fas fa-search me-2"></i>
                BUSCAR
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Fin de buscador -->

  <!-- Inicio de temporizador -->
  <div class="timer-container" id="timer-container">
  </div>
  <!-- Fin de temporizador -->

  <!-- Pasos de la compra -->
  <div class="progress-header py-3 bg-light" id="indicador-progreso">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12">
          <div class="progress-steps-wrapper">
            <div class="progress-steps">
              <div class="step active">
                <div class="step-content">
                  <span class="step-number">1</span>
                  <span class="step-text">Seleccionar viaje</span>
                </div>
              </div>
              <div class="step-connector"></div>
              <div class="step">
                <div class="step-content">
                  <span class="step-number">2</span>
                  <span class="step-text">Elegir asientos</span>
                </div>
              </div>
              <div class="step-connector"></div>
              <div class="step">
                <div class="step-content">
                  <span class="step-number">3</span>
                  <span class="step-text">Datos pasajero</span>
                </div>
              </div>
              <div class="step-connector"></div>
              <div class="step">
                <div class="step-content">
                  <span class="step-number">4</span>
                  <span class="step-text">Pago</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Fin de pasos de compra -->

  <!-- Inicio de div para prgramaciones -->
  <div id="contenedor-programaciones" class="py-5" style="display:none;">
    <div class="container">
      <!-- Contenedor de programaciones - cambiar estructura -->
      <div id="lista-programaciones">
        <!-- Las programaciones se inyectarán aquí con JavaScript -->
      </div>
    </div>
  </div>
  <!-- Fin de div para programaciones -->

  <!-- Inicio de seccion pagos -->
  <div class="bus-payment-container d-none" id="contenedor-pagos">
    <div class="bus-payment-form">
      <div class="bus-payment-header">
        <h1><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
        <p>Completa los datos de facturación y selecciona tu método de pago</p>
      </div>

      <!-- Datos de Facturación -->
      <div class="bus-payment-section">
        <div class="bus-billing-header">
          <h3 class="bus-section-title">
            <i class="fas fa-file-invoice"></i>
            Datos de facturación
          </h3>
        </div>
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <span style="color: #718096; font-size: 14px;">Necesito Factura</span>
          <div class="bus-toggle-switch">
            <input type="checkbox" class="bus-toggle-input" id="bus-factura">
            <span class="bus-toggle-slider"></span>
          </div>
        </label>
        <div class="bus-form-grid d-none" id="datos_facturacion">
          <div class="bus-form-group">
            <label class="bus-form-label">RUC</label>
            <input type="text" class="bus-form-input" placeholder="Número de RUC" id="ruc_factura" maxlength="11" onkeypress="return controlTag(event);">
          </div>
          <div class="bus-form-group">
            <label class="bus-form-label">Razón Social</label>
            <input type="hidden" name="direccion_factura" id="direccion_factura">
            <input type="hidden" name="ubigeo_factura" id="ubigeo_factura">
            <input type="hidden" name="estado_factura" id="estado_factura">
            <input type="hidden" name="condicion_factura" id="condicion_factura">
            <input type="text" class="bus-form-input" placeholder="Razón social" id="razon_social_factura" readonly>
          </div>
        </div>
      </div>

      <!-- Datos del Comprador -->
      <div class="bus-payment-section">
        <div class="bus-billing-header">
          <h3 class="bus-section-title">
            <i class="fas fa-user"></i>
            Datos del comprador
          </h3>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
          <p style="color: #718096; font-size: 14px; margin: 0;">Usar datos del primer pasajero</p>
          <label class="bus-toggle-switch">
            <input type="checkbox" class="bus-toggle-input" id="bus-comprador">
            <span class="bus-toggle-slider"></span>
          </label>
        </div>

        <div class="bus-form-grid" id="datos_comprador">
          <div class="bus-form-group">
            <label class="bus-form-label">Documento</label>
            <input type="text" class="bus-form-input" placeholder="Numero de DNI" id="numero_documento_comprador" name="numero_documento_comprador" maxlength="8" onkeypress="return controlTag(event);">
          </div>
          <div class="bus-form-group">
            <label class="bus-form-label">Nombre(s)</label>
            <input type="text" class="bus-form-input" placeholder="Nombres completos" id="nombres_comprador" name="nombres_comprador">
          </div>
          <div class="bus-form-group">
            <label class="bus-form-label">Apellidos</label>
            <input type="text" class="bus-form-input" placeholder="Apellido paterno" id="apellidos_comprador" name="apellidos_comprador">
          </div>
          <div class="bus-form-group">
            <label class="bus-form-label">Teléfono celular</label>
            <input type="tel" class="bus-form-input" placeholder="Número de teléfono" id="telefono_comprador" name="telefono_comprador" maxlength="9" onkeypress="return controlTag(event);">
          </div>
          <div class="bus-form-group">
            <label class="bus-form-label">Correo electrónico</label>
            <input type="email" class="bus-form-input" placeholder="tu@email.com" id="correo_comprador" name="correo_comprador">
          </div>
        </div>
      </div>

      <!-- Métodos de Pago -->
      <div class="bus-payment-section">
        <h3 class="bus-section-title">
          <i class="fas fa-wallet"></i>
          Selecciona tu forma de pago
        </h3>

        <div class="bus-payment-methods">
          <div class="bus-payment-option" id="tarjeta">
            <div class="bus-payment-icon">
              <i class="fab fa-cc-visa" style="font-size: 30px; color: #1a1f71;"></i>
              <i class="fab fa-cc-amex" style="font-size: 30px; color: #006fcf; margin-left: 10px;"></i>
            </div>
            <div class="bus-payment-label">Crédito o débito</div>
          </div>

          <div class="bus-payment-option" id="yape-codigo">
            <div class="bus-payment-icon">
              <img src="<?= URL_IMAGEN_VENTA_WEB ?>logo_yape.png" alt="Logo de yape">
            </div>
            <div class="bus-payment-label">Yape</div>
          </div>

          <!-- <div class="bus-payment-option">
            <div class="bus-payment-icon">
              <img src="<?= URL_IMAGEN_WEB ?>bcp.png" alt="Logo de bcp">
            </div>
            <div class="bus-payment-label">BCP</div>
          </div> -->

          <!-- <div class="bus-payment-option bus-selected">
            <div class="bus-payment-icon">
              <i class="fas fa-money-bill-wave" style="font-size: 30px; color: #48bb78;"></i>
            </div>
            <div class="bus-payment-label">Efectivo / Transferencia</div>
          </div> -->
        </div>
      </div>
    </div>

    <div class="bus-payment-summary">
      <!-- Resumen del Viaje -->
      <div class="bus-summary-card" id="informacion_general_pago">

      </div>

      <!-- Información de Pasajeros -->
      <div class="bus-summary-card" id="informacion_pasajeros">

      </div>

      <!-- Desglose de Precios -->
      <div class="bus-summary-card" id="informacion_precio">

      </div>

      <button class="bus-btn-secondary" id="btn-volver-pasajeros">
        <i class="fas fa-arrow-left"></i> Volver a datos de pasajeros
      </button>

      <button class="bus-pay-btn" id="btn-pagar">
        <i class="fas fa-lock"></i> Proceder al Pago
      </button>

      <div class="bus-security-info">
        <i class="fas fa-shield-alt"></i>
        <span class="bus-security-text">Pago 100% seguro. Tus datos están protegidos con encriptación
          SSL.</span>
      </div>
    </div>
  </div>
  <!-- Fin de seccion pagos -->

  <!-- Modal Términos y Condiciones -->
  <div
    class="modal fade"
    id="modalTerminosCondiciones"
    tabindex="-1"
    aria-labelledby="modalTerminosCondicionesLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header modal-terminos-header">

          <!-- Logo -->
          <div class="modal-terminos-logo">
            <img
              src="<?= URL_IMAGEN_ADMIN ?>logo_tisoc.png"
              alt="TISOC">
          </div>

          <!-- Título -->
          <h5
            class="modal-title"
            id="modalTerminosCondicionesLabel">
            Términos y Condiciones
          </h5>

          <!-- Cerrar -->
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-0">
          <iframe
            src="<?= URL_VENTA_WEB; ?>pdf/ClausulasVentaPasajesWeb.pdf"
            id="iframeTerminosCondiciones"
            width="100%"
            style="border: none;"
            title="Términos y condiciones de venta"></iframe>
        </div>

      </div>
    </div>
  </div>
  <script>
    function controlTag(e) {
      tecla = (document.all) ? e.keyCode : e.which;
      if (tecla == 8) return true;
      else if (tecla == 0 || tecla == 9) return true;
      patron = /[0-9\s]/;
      n = String.fromCharCode(tecla);
      return patron.test(n);
    }
  </script>
  <?php
  $version = "1.0.9";
  ?>
  <script src="<?php echo URL; ?>public/plugins/sweetalert2/sweetalert2@11.js"></script>
  <!-- Bootstrap JS (asegúrate de incluir Popper.js) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
  <script src="https://3ds.culqi.com" defer></script>
</body>

</html>